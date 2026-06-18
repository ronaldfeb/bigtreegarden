<?php

namespace App\Models;

use Database\Factories\AmbassadorFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'title',
    'description',
    'status',
    'handle_linkedin',
    'handle_facebook',
    'handle_instagram',
    'website_url',
])]
class Ambassador extends Model
{
    /** @use HasFactory<AmbassadorFactory> */
    use HasFactory, HasUuids;

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
