<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'marketing_advert_id',
    'visitor_hash',
    'ip_address',
    'user_agent',
    'referrer',
    'visited_at',
])]
class MarketingAdvertVisit extends Model
{
    use HasUuids;

    /**
     * @return BelongsTo<MarketingAdvert, $this>
     */
    public function advert(): BelongsTo
    {
        return $this->belongsTo(MarketingAdvert::class, 'marketing_advert_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'visited_at' => 'datetime',
        ];
    }
}
