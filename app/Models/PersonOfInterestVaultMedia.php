<?php

namespace App\Models;

use App\Concerns\HasDomainTimestamps;
use Database\Factories\PersonOfInterestVaultMediaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'vault_id',
    'uploaded_by_user_id',
    'type',
    'title',
    'file_path',
    'mime_type',
    'file_size_bytes',
    'duration_seconds',
])]
class PersonOfInterestVaultMedia extends Model
{
    /** @use HasFactory<PersonOfInterestVaultMediaFactory> */
    use HasDomainTimestamps, HasFactory, HasUuids;

    protected function casts(): array
    {
        return [
            'file_size_bytes' => 'integer',
            'duration_seconds' => 'integer',
        ];
    }

    public function vault(): BelongsTo
    {
        return $this->belongsTo(PersonOfInterestVault::class, 'vault_id');
    }

    public function uploadedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }
}
