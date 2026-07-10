<?php

namespace App\Models;

use App\Concerns\HasDomainTimestamps;
use Database\Factories\PersonOfInterestVaultBeneficiaryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable([
    'vault_id',
    'type',
    'full_name',
    'email',
    'contact_number',
    'physical_address',
    'access_code_hash',
    'access_code_hint',
    'first_accessed_at',
])]
class PersonOfInterestVaultBeneficiary extends Model
{
    /** @use HasFactory<PersonOfInterestVaultBeneficiaryFactory> */
    use HasDomainTimestamps, HasFactory, HasUuids;

    protected function casts(): array
    {
        return [
            'first_accessed_at' => 'datetime',
        ];
    }

    public function vault(): BelongsTo
    {
        return $this->belongsTo(PersonOfInterestVault::class, 'vault_id');
    }

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(
            PersonOfInterestVaultPost::class,
            'person_of_interest_vault_post_beneficiaries',
            'vault_beneficiary_id',
            'vault_post_id',
        )
            ->using(PersonOfInterestVaultPostBeneficiary::class)
            ->withTimestamps();
    }
}
