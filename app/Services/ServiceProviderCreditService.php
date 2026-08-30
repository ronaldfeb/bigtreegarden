<?php

namespace App\Services;

use App\Enums\ServiceProviderCreditPurchaseStatus;
use App\Enums\TransactionStatus;
use App\Models\MemorialPage;
use App\Models\ServiceProvider;
use App\Models\ServiceProviderCreditLedgerEntry;
use App\Models\ServiceProviderCreditPurchase;
use App\Models\StaffUser;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class ServiceProviderCreditService
{
    public function __construct(private DiscountCodeService $discountCodeService) {}

    public function releasePurchase(ServiceProviderCreditPurchase $purchase, ?User $actor = null, ?StaffUser $staffUser = null, ?string $note = null): void
    {
        DB::transaction(function () use ($purchase, $actor, $staffUser, $note): void {
            /** @var ServiceProviderCreditPurchase $lockedPurchase */
            $lockedPurchase = ServiceProviderCreditPurchase::query()
                ->whereKey($purchase->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedPurchase->status === ServiceProviderCreditPurchaseStatus::Released) {
                return;
            }

            if (! in_array($lockedPurchase->status, [
                ServiceProviderCreditPurchaseStatus::PendingPayment,
                ServiceProviderCreditPurchaseStatus::PendingReview,
            ], true)) {
                throw new InvalidArgumentException('Only pending purchases can be released.');
            }

            /** @var ServiceProvider $provider */
            $provider = ServiceProvider::query()
                ->whereKey($lockedPurchase->service_provider_id)
                ->lockForUpdate()
                ->firstOrFail();

            $newBalance = $provider->credits_remaining + $lockedPurchase->page_count;

            $provider->update(['credits_remaining' => $newBalance]);

            ServiceProviderCreditLedgerEntry::query()->create([
                'service_provider_id' => $provider->id,
                'delta' => $lockedPurchase->page_count,
                'balance_after' => $newBalance,
                'reason' => 'purchase_released',
                'reference_type' => $lockedPurchase->getMorphClass(),
                'reference_id' => $lockedPurchase->id,
                'created_by_user_id' => $actor?->id,
            ]);

            $lockedPurchase->update([
                'status' => ServiceProviderCreditPurchaseStatus::Released,
                'released_at' => now(),
                'reviewed_by_staff_user_id' => $staffUser?->id ?? $lockedPurchase->reviewed_by_staff_user_id,
                'reviewed_at' => $staffUser !== null ? now() : $lockedPurchase->reviewed_at,
                'review_note' => $note ?? $lockedPurchase->review_note,
            ]);

            $transaction = $lockedPurchase->transactions()
                ->whereNotNull('discount_code_id')
                ->latest()
                ->first();

            if ($transaction !== null) {
                $this->discountCodeService->consume($transaction, $actor ?? $lockedPurchase->purchasedBy);
            }
        });
    }

    public function rejectPurchase(ServiceProviderCreditPurchase $purchase, StaffUser $staffUser, ?string $note = null): void
    {
        DB::transaction(function () use ($purchase, $staffUser, $note): void {
            /** @var ServiceProviderCreditPurchase $lockedPurchase */
            $lockedPurchase = ServiceProviderCreditPurchase::query()
                ->whereKey($purchase->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedPurchase->status !== ServiceProviderCreditPurchaseStatus::PendingReview) {
                throw new InvalidArgumentException('Only purchases pending review can be rejected.');
            }

            $transaction = $lockedPurchase->transactions()
                ->where('status', TransactionStatus::Initiated)
                ->latest()
                ->first();

            if ($transaction !== null) {
                $this->discountCodeService->release($transaction);
            }

            $lockedPurchase->update([
                'status' => ServiceProviderCreditPurchaseStatus::Rejected,
                'reviewed_by_staff_user_id' => $staffUser->id,
                'reviewed_at' => now(),
                'review_note' => $note,
            ]);
        });
    }

    public function consumeCredit(ServiceProvider $serviceProvider, MemorialPage $memorialPage, User $actor): void
    {
        DB::transaction(function () use ($serviceProvider, $memorialPage, $actor): void {
            /** @var ServiceProvider $provider */
            $provider = ServiceProvider::query()
                ->whereKey($serviceProvider->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($provider->credits_remaining < 1) {
                throw new RuntimeException('Insufficient memorial page credits.');
            }

            $newBalance = $provider->credits_remaining - 1;

            $provider->update(['credits_remaining' => $newBalance]);

            ServiceProviderCreditLedgerEntry::query()->create([
                'service_provider_id' => $provider->id,
                'delta' => -1,
                'balance_after' => $newBalance,
                'reason' => 'memorial_created',
                'reference_type' => $memorialPage->getMorphClass(),
                'reference_id' => $memorialPage->id,
                'created_by_user_id' => $actor->id,
            ]);
        });
    }
}
