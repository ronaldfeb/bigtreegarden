<?php

namespace App\Models;

use App\Enums\ServiceProviderCreditPaymentMethod;
use App\Enums\ServiceProviderCreditPurchaseStatus;
use Database\Factories\ServiceProviderCreditPurchaseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'service_provider_id',
    'service_provider_credit_package_id',
    'purchased_by_user_id',
    'package_name',
    'page_count',
    'price_cents',
    'currency',
    'payment_method',
    'status',
    'payment_reference',
    'proof_of_payment_path',
    'reviewed_by_staff_user_id',
    'reviewed_at',
    'review_note',
    'released_at',
])]
class ServiceProviderCreditPurchase extends Model
{
    /** @use HasFactory<ServiceProviderCreditPurchaseFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'page_count' => 'integer',
            'price_cents' => 'integer',
            'payment_method' => ServiceProviderCreditPaymentMethod::class,
            'status' => ServiceProviderCreditPurchaseStatus::class,
            'reviewed_at' => 'datetime',
            'released_at' => 'datetime',
        ];
    }

    public function serviceProvider(): BelongsTo
    {
        return $this->belongsTo(ServiceProvider::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(ServiceProviderCreditPackage::class, 'service_provider_credit_package_id');
    }

    public function purchasedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'purchased_by_user_id');
    }

    public function reviewedByStaffUser(): BelongsTo
    {
        return $this->belongsTo(StaffUser::class, 'reviewed_by_staff_user_id');
    }

    public function transactions(): MorphMany
    {
        return $this->morphMany(Transaction::class, 'payable');
    }
}
