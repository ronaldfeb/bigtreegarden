<?php

namespace App\Models;

use App\Concerns\HasDomainTimestamps;
use Database\Factories\MemorialSiteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'person_of_interest_id',
    'name',
    'site_type',
    'description',
    'address',
    'latitude',
    'longitude',
    'geofence_radius_m',
])]
class MemorialSite extends Model
{
    /** @use HasFactory<MemorialSiteFactory> */
    use HasDomainTimestamps, HasFactory, HasUuids;

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'geofence_radius_m' => 'integer',
        ];
    }

    public function personOfInterest(): BelongsTo
    {
        return $this->belongsTo(PersonOfInterest::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(MemorialPageMessage::class);
    }
}
