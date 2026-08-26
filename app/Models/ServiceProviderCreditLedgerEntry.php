<?php

namespace App\Models;

use Database\Factories\ServiceProviderCreditLedgerEntryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
    'service_provider_id',
    'delta',
    'balance_after',
    'reason',
    'reference_type',
    'reference_id',
    'created_by_user_id',
])]
class ServiceProviderCreditLedgerEntry extends Model
{
    /** @use HasFactory<ServiceProviderCreditLedgerEntryFactory> */
    use HasFactory, HasUuids;

    protected $table = 'service_provider_credit_ledger';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'delta' => 'integer',
            'balance_after' => 'integer',
        ];
    }

    public function serviceProvider(): BelongsTo
    {
        return $this->belongsTo(ServiceProvider::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
