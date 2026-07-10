<?php

namespace App\Models;

use Database\Factories\MarketingLeadFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'staff_user_id',
    'marketing_advert_id',
    'name',
    'email',
    'phone',
    'organisation',
    'source',
    'status',
])]
class MarketingLead extends Model
{
    /** @use HasFactory<MarketingLeadFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    public function staffUser(): BelongsTo
    {
        return $this->belongsTo(StaffUser::class);
    }

    public function marketingAdvert(): BelongsTo
    {
        return $this->belongsTo(MarketingAdvert::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(MarketingLeadNote::class);
    }
}
