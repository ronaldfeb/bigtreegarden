<?php

namespace App\Models;

use Database\Factories\ServiceProviderCreditPackageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'name',
    'slug',
    'description',
    'page_count',
    'price_cents',
    'currency',
    'is_active',
    'sort_order',
])]
class ServiceProviderCreditPackage extends Model
{
    /** @use HasFactory<ServiceProviderCreditPackageFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'page_count' => 'integer',
            'price_cents' => 'integer',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(ServiceProviderCreditPurchase::class);
    }

    /**
     * @param  Builder<ServiceProviderCreditPackage>  $query
     * @return Builder<ServiceProviderCreditPackage>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
