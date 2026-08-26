<?php

namespace App\Models;

use Database\Factories\ServiceProviderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'name',
    'slug',
    'registration_number',
    'vat_number',
    'description',
    'logo_path',
    'cover_image_path',
    'email',
    'phone',
    'website_url',
    'physical_address',
    'city',
    'province',
    'status',
    'credits_remaining',
])]
class ServiceProvider extends Model
{
    /** @use HasFactory<ServiceProviderFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'credits_remaining' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'service_provider_users')
            ->using(ServiceProviderUser::class)
            ->withPivot(['role'])
            ->withTimestamps();
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(ServiceProviderUser::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(ServiceProviderInvitation::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(ServiceProviderService::class)->orderBy('sort_order');
    }

    public function specialities(): HasMany
    {
        return $this->hasMany(ServiceProviderSpeciality::class)->orderBy('sort_order');
    }

    public function socialMedia(): HasMany
    {
        return $this->hasMany(ServiceProviderSocialMedia::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ServiceProviderImage::class)->orderBy('sort_order');
    }

    public function backgrounds(): HasMany
    {
        return $this->hasMany(ServiceProviderBackground::class)->orderBy('sort_order');
    }

    public function creditPurchases(): HasMany
    {
        return $this->hasMany(ServiceProviderCreditPurchase::class);
    }

    public function creditLedgerEntries(): HasMany
    {
        return $this->hasMany(ServiceProviderCreditLedgerEntry::class);
    }

    public function personsOfInterest(): HasMany
    {
        return $this->hasMany(PersonOfInterest::class);
    }

    public function isActive(): bool
    {
        return $this->status === config('constants.service_provider.status.active');
    }

    public function isPending(): bool
    {
        return $this->status === config('constants.service_provider.status.pending');
    }

    public function isSuspended(): bool
    {
        return $this->status === config('constants.service_provider.status.suspended');
    }

    /**
     * @param  Builder<ServiceProvider>  $query
     * @return Builder<ServiceProvider>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', config('constants.service_provider.status.active'));
    }
}
