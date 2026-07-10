<?php

namespace App\Models;

use Database\Factories\ServiceProviderFactory;
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
    'registration_number',
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
])]
class ServiceProvider extends Model
{
    /** @use HasFactory<ServiceProviderFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    public function getRouteKeyName(): string
    {
        return 'slug';
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

    /**
     * @param  Builder<ServiceProvider>  $query
     * @return Builder<ServiceProvider>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', config('constants.service_provider.status.active'));
    }
}
