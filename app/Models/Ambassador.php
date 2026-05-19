<?php

namespace App\Models;

use Database\Factories\AmbassadorFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name',
    'title',
    'description',
    'image_url',
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

    /**
     * @param  Builder<Ambassador>  $query
     * @return Builder<Ambassador>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', config('constants.ambassador.status.active'));
    }
}
