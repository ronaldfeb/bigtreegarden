<?php

namespace App\Models;

use App\Support\MemorialPackageSession;
use Database\Factories\SubscriptionPackageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

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
     * Replace package features with the given rows, preserving existing ids when provided.
     *
     * @param  list<array{id?: string|null, label: string, description?: string|null, is_included?: mixed}>  $features
     */
    public function syncFeatures(array $features): void
    {
        $keepIds = [];

        foreach (array_values($features) as $index => $feature) {
            $payload = [
                'label' => $feature['label'],
                'description' => $feature['description'] ?? null,
                'is_included' => (bool) ($feature['is_included'] ?? true),
                'sort_order' => $index,
            ];

            $id = $feature['id'] ?? null;

            if (is_string($id) && $id !== '') {
                $existing = $this->features()->whereKey($id)->first();

                if ($existing !== null) {
                    $existing->update($payload);
                    $keepIds[] = $existing->id;

                    continue;
                }
            }

            $keepIds[] = $this->features()->create($payload)->id;
        }

        $obsolete = $keepIds === []
            ? $this->features()->get()
            : $this->features()->whereNotIn('id', $keepIds)->get();

        $obsolete->each->delete();
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
            ->whereIn('slug', ['funeral-memorial', 'memorial-page'])
            ->orderByRaw("case when slug = 'funeral-memorial' then 0 else 1 end")
            ->first()
            ?? static::query()
                ->active()
                ->where('billing_interval', 'once_off')
                ->orderBy('sort_order')
                ->first();
    }

    /**
     * Once-off package selected for this session, falling back to Funeral Memorial.
     */
    public static function selectedOnceOffPackage(): ?self
    {
        $slug = MemorialPackageSession::slug();

        if ($slug !== null) {
            $fromSession = static::query()
                ->active()
                ->where('billing_interval', 'once_off')
                ->where('slug', $slug)
                ->first();

            if ($fromSession !== null) {
                return $fromSession;
            }
        }

        return static::memorialPagePackage();
    }

    public static function livingLegacyPackage(): ?self
    {
        return static::query()
            ->active()
            ->where('slug', 'living-legacy')
            ->where('billing_interval', '!=', 'once_off')
            ->first()
            ?? static::query()
                ->active()
                ->where('billing_interval', '!=', 'once_off')
                ->orderBy('sort_order')
                ->first();
    }

    /**
     * @return array{name: string, price_cents: int, currency: string, billing_interval: string}
     */
    public function pricingPayload(): array
    {
        return [
            'name' => $this->name,
            'price_cents' => $this->price_cents,
            'currency' => $this->currency,
            'billing_interval' => $this->billing_interval,
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public static function marketingOfferings(): Collection
    {
        return static::query()
            ->active()
            ->with(['features' => fn ($query) => $query->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get()
            ->map(fn (self $package): array => [
                'id' => $package->id,
                'name' => $package->name,
                'slug' => $package->slug,
                'description' => $package->description,
                'price_cents' => $package->price_cents,
                'currency' => $package->currency,
                'billing_interval' => $package->billing_interval,
                'is_featured' => $package->is_featured,
                'features' => $package->features->map(fn (SubscriptionPackageFeature $feature): array => [
                    'id' => $feature->id,
                    'label' => $feature->label,
                    'description' => $feature->description,
                    'is_included' => $feature->is_included,
                ])->values()->all(),
            ]);
    }
}
