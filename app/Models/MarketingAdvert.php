<?php

namespace App\Models;

use Database\Factories\MarketingAdvertFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'staff_user_id',
    'client_name',
    'campaign_name',
    'code',
    'destination_url',
    'utm_source',
    'utm_medium',
    'utm_campaign',
    'utm_content',
    'qr_code_path',
    'status',
    'starts_at',
    'ends_at',
])]
class MarketingAdvert extends Model
{
    /** @use HasFactory<MarketingAdvertFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * @return BelongsTo<StaffUser, $this>
     */
    public function staffUser(): BelongsTo
    {
        return $this->belongsTo(StaffUser::class);
    }

    /**
     * @return HasMany<MarketingAdvertVisit, $this>
     */
    public function visits(): HasMany
    {
        return $this->hasMany(MarketingAdvertVisit::class);
    }

    /**
     * @param  Builder<MarketingAdvert>  $query
     * @return Builder<MarketingAdvert>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where('status', config('constants.marketing_advert.status.active'))
            ->where(function (Builder $query): void {
                $query
                    ->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function (Builder $query): void {
                $query
                    ->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            });
    }
}
