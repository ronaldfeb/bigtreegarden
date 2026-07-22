<?php

namespace App\Models;

use Database\Factories\SubscriptionPackageFactory;
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
    'price_cents',
    'currency',
    'billing_interval',
    'is_featured',
    'is_active',
    'sort_order',
])]
class SubscriptionPackage extends Model
{
    /** @use HasFactory<SubscriptionPackageFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function features(): HasMany
    {
        return $this->hasMany(SubscriptionPackageFeature::class)->orderBy('sort_order');
    }

    /**
     * @param  Builder<SubscriptionPackage>  $query
     * @return Builder<SubscriptionPackage>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * The once-off package that prices memorial pamphlet purchases.
     */
    public static function memorialPagePackage(): ?self
    {
        return static::query()
            ->active()
            ->where('billing_interval', 'once_off')
            ->where('slug', 'memorial-page')
            ->first()
            ?? static::query()
                ->active()
                ->where('billing_interval', 'once_off')
                ->orderBy('sort_order')
                ->first();
    }
}
