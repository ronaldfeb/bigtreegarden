<?php

namespace App\Models;

use App\Concerns\HasDomainTimestamps;
use Database\Factories\PersonOfInterestVaultFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'person_of_interest_id',
    'name',
    'status',
    'released_at',
    'storage_limit_mb',
])]
class PersonOfInterestVault extends Model
{
    /** @use HasFactory<PersonOfInterestVaultFactory> */
    use HasDomainTimestamps, HasFactory, HasUuids;

    protected function casts(): array
    {
        return [
            'released_at' => 'datetime',
            'storage_limit_mb' => 'integer',
        ];
    }

    public function isManagedBy(?User $user): bool
    {
        if ($user === null) {
            return false;
        }

        return $this->personOfInterest
            ->users()
            ->whereKey($user->id)
            ->exists();
    }

    public function isReleased(): bool
    {
        return $this->status === 'released';
    }

    public function usedStorageBytes(): int
    {
        return (int) $this->media()->sum('file_size_bytes');
    }

    public function storageLimitBytes(): int
    {
        return $this->storage_limit_mb * 1024 * 1024;
    }

    public function personOfInterest(): BelongsTo
    {
        return $this->belongsTo(PersonOfInterest::class);
    }

    public function beneficiaries(): HasMany
    {
        return $this->hasMany(PersonOfInterestVaultBeneficiary::class, 'vault_id');
    }

    public function media(): HasMany
    {
        return $this->hasMany(PersonOfInterestVaultMedia::class, 'vault_id');
    }

    public function posts(): HasMany
    {
        return $this->hasMany(PersonOfInterestVaultPost::class, 'vault_id');
    }
}
