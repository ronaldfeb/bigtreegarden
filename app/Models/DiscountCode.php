<?php

namespace App\Models;

use App\Enums\DiscountType;
use Database\Factories\DiscountCodeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

#[Fillable([
    'code',
    'discount_type',
    'percent',
    'amount_cents',
    'starts_at',
    'ends_at',
    'applies_to_all',
    'discountable_type',
    'discountable_id',
    'reserved_transaction_id',
    'reserved_at',
    'used_at',
    'used_by_user_id',
    'used_transaction_id',
    'created_by_staff_user_id',
])]
class DiscountCode extends Model
{
    /** @use HasFactory<DiscountCodeFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'discount_type' => DiscountType::class,
            'percent' => 'integer',
            'amount_cents' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'applies_to_all' => 'boolean',
            'reserved_at' => 'datetime',
            'used_at' => 'datetime',
        ];
    }

    public function discountable(): MorphTo
    {
        return $this->morphTo();
    }

    public function createdByStaffUser(): BelongsTo
    {
        return $this->belongsTo(StaffUser::class, 'created_by_staff_user_id');
    }

    public function reservedTransaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'reserved_transaction_id');
    }

    public function usedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'used_by_user_id');
    }

    public function usedTransaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'used_transaction_id');
    }

    public static function generate(): string
    {
        do {
            $code = Str::upper(Str::random(8));
            $code = preg_replace('/[^A-Z0-9]/', '', $code) ?? '';

            while (strlen($code) < 8) {
                $code .= Str::upper(Str::random(1));
                $code = preg_replace('/[^A-Z0-9]/', '', $code) ?? '';
            }

            $code = substr($code, 0, 8);
        } while (static::query()->where('code', $code)->exists());

        return $code;
    }

    public static function normalize(?string $value): string
    {
        return Str::upper(preg_replace('/[^A-Za-z0-9]/', '', (string) $value) ?? '');
    }

    public function formattedCode(): string
    {
        $code = $this->code;

        if (strlen($code) !== 8) {
            return $code;
        }

        return substr($code, 0, 2).'-'.substr($code, 2, 4).'-'.substr($code, 6, 2);
    }

    public function isUsable(?Transaction $forTransaction = null): bool
    {
        try {
            $this->assertUsable($forTransaction);

            return true;
        } catch (ValidationException) {
            return false;
        }
    }

    /**
     * @throws ValidationException
     */
    public function assertUsable(?Transaction $forTransaction = null): void
    {
        if ($this->used_at !== null) {
            throw ValidationException::withMessages([
                'code' => 'This discount code has already been used.',
            ]);
        }

        if ($this->starts_at->isFuture()) {
            throw ValidationException::withMessages([
                'code' => 'This discount code is not active yet.',
            ]);
        }

        if ($this->ends_at->isPast()) {
            throw ValidationException::withMessages([
                'code' => 'This discount code has expired.',
            ]);
        }

        if ($this->reserved_transaction_id !== null
            && ($forTransaction === null || $this->reserved_transaction_id !== $forTransaction->id)) {
            throw ValidationException::withMessages([
                'code' => 'This discount code is reserved on another checkout.',
            ]);
        }
    }

    public function appliesTo(Model $target): bool
    {
        if ($this->applies_to_all) {
            return $target instanceof SubscriptionPackage
                || $target instanceof ServiceProviderCreditPackage;
        }

        if ($this->discountable_type !== $target::class) {
            return false;
        }

        if ($this->discountable_id === $target->getKey()) {
            return true;
        }

        if ($target instanceof SubscriptionPackage) {
            $configuredPackage = $this->relationLoaded('discountable')
                ? $this->discountable
                : $this->discountable()->first();

            if ($configuredPackage instanceof SubscriptionPackage) {
                return $this->subscriptionPackagesMatch($configuredPackage, $target);
            }
        }

        return false;
    }

    /**
     * @throws ValidationException
     */
    public function assertAppliesTo(Model $target): void
    {
        if ($this->appliesTo($target)) {
            return;
        }

        $targetLabel = match (true) {
            $target instanceof SubscriptionPackage => $target->name,
            $target instanceof ServiceProviderCreditPackage => $target->name,
            default => 'this purchase',
        };

        if ($this->applies_to_all) {
            throw ValidationException::withMessages([
                'code' => 'This discount code does not apply to this purchase.',
            ]);
        }

        $configuredPackage = $this->relationLoaded('discountable')
            ? $this->discountable
            : $this->discountable()->first();

        $configuredLabel = match (true) {
            $configuredPackage instanceof SubscriptionPackage,
            $configuredPackage instanceof ServiceProviderCreditPackage => $configuredPackage->name,
            default => 'another package',
        };

        throw ValidationException::withMessages([
            'code' => "This discount code applies to {$configuredLabel}, not {$targetLabel}.",
        ]);
    }

    private function subscriptionPackagesMatch(SubscriptionPackage $configured, SubscriptionPackage $target): bool
    {
        if ($configured->slug === $target->slug) {
            return true;
        }

        $equivalentSlugs = [
            'memorial-page' => 'funeral-memorial',
        ];

        return ($equivalentSlugs[$configured->slug] ?? null) === $target->slug
            || ($equivalentSlugs[$target->slug] ?? null) === $configured->slug;
    }

    public function discountCents(int $listPriceCents): int
    {
        $discount = match ($this->discount_type) {
            DiscountType::Percent => (int) round($listPriceCents * (($this->percent ?? 0) / 100)),
            DiscountType::Fixed => (int) ($this->amount_cents ?? 0),
        };

        return max(0, min($listPriceCents, $discount));
    }

    public function payableCents(int $listPriceCents): int
    {
        return max(0, $listPriceCents - $this->discountCents($listPriceCents));
    }

    public function statusLabel(): string
    {
        if ($this->used_at !== null) {
            return 'used';
        }

        if ($this->ends_at->isPast()) {
            return 'expired';
        }

        if ($this->starts_at->isFuture()) {
            return 'scheduled';
        }

        if ($this->reserved_transaction_id !== null) {
            return 'reserved';
        }

        return 'active';
    }
}
