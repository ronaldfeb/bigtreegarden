<?php

namespace App\Models;

use App\Concerns\HasDomainTimestamps;
use App\Enums\MemorialPageStatus;
use Database\Factories\MemorialPageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'person_of_interest_id',
    'title',
    'public_slug',
    'status',
    'active_day_type',
    'active_day_date',
    'gallery_enabled',
    'live_comments_enabled',
    'published_at',
])]
class MemorialPage extends Model
{
    /** @use HasFactory<MemorialPageFactory> */
    use HasDomainTimestamps, HasFactory, HasUuids;

    protected function casts(): array
    {
        return [
            'active_day_date' => 'date',
            'gallery_enabled' => 'boolean',
            'live_comments_enabled' => 'boolean',
            'published_at' => 'datetime',
            'status' => MemorialPageStatus::class,
        ];
    }

    public function personOfInterest(): BelongsTo
    {
        return $this->belongsTo(PersonOfInterest::class);
    }

    public function pamphlet(): HasOne
    {
        return $this->hasOne(MemorialPagePamphlet::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(MemorialPageImage::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(MemorialPageSection::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(MemorialPageMessage::class);
    }
}
