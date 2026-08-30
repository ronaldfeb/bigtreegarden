<?php

namespace App\Services;

use App\Enums\PamphletStatus;
use App\Enums\TransactionStatus;
use App\Models\MemorialPagePamphlet;
use App\Models\Transaction;

class PamphletPaymentFulfillmentService
{
    public function __construct(
        private QrCodeService $qrCodeService,
        private DiscountCodeService $discountCodeService,
    ) {}

    /**
     * @param  array<string, mixed>  $rawPayload
     */
    public function fulfill(
        MemorialPagePamphlet $pamphlet,
        Transaction $transaction,
        array $rawPayload = [],
        ?string $providerPaymentId = null,
    ): void {
        if (in_array($pamphlet->status, [PamphletStatus::Paid, PamphletStatus::Published], true)) {
            return;
        }

        $transaction->update([
            'provider_payment_id' => $providerPaymentId,
            'status' => TransactionStatus::Complete,
            'paid_at' => now(),
            'raw_payload' => $rawPayload,
        ]);

        $this->discountCodeService->consume($transaction->fresh());

        $pamphlet->update([
            'status' => PamphletStatus::Paid,
            'paid_at' => now(),
        ]);

        $memorialPage = $pamphlet->memorialPage;
        $personOfInterest = $memorialPage?->personOfInterest;

        if ($memorialPage === null || $personOfInterest === null) {
            return;
        }

        $targetUrl = route('memorial.public.show', $memorialPage->public_slug);

        $personOfInterest->update([
            'qr_code_path' => $this->qrCodeService->imageUrlForTarget($targetUrl),
            'qr_generated_at' => now(),
        ]);
    }
}
