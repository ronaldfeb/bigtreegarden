<?php

namespace App\Models;

use Database\Factories\AmbassadorFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'name',
    'slug',
    'title',
    'description',
    'status',
    'handle_linkedin',
    'handle_facebook',
    'handle_instagram',
    'website_url',
    'profile_image_path',
    'sort_order',
])]
class Ambassador extends Model
{
    /** @use HasFactory<AmbassadorFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    protected static function booted(): void
    {
        static::creating(function (Ambassador $ambassador): void {
            if (blank($ambassador->slug)) {
                $ambassador->slug = Str::slug($ambassador->name);
            }
        });
    }

    public function images(): HasMany
    {
        return $this->hasMany(AmbassadorImage::class)->orderBy('sort_order');
    }

    /**
     * @param  Builder<Ambassador>  $query
     * @return Builder<Ambassador>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', config('constants.ambassador.status.active'));
    }
}
