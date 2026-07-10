<?php

namespace App\Models;

use App\Concerns\HasDomainTimestamps;
use Database\Factories\PersonOfInterestVaultPostFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable([
    'vault_id',
    'author_user_id',
    'title',
    'body',
    'visibility',
])]
class PersonOfInterestVaultPost extends Model
{
    /** @use HasFactory<PersonOfInterestVaultPostFactory> */
    use HasDomainTimestamps, HasFactory, HasUuids;

    public function vault(): BelongsTo
    {
        return $this->belongsTo(PersonOfInterestVault::class, 'vault_id');
    }

    public function authorUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }

    public function beneficiaries(): BelongsToMany
    {
        return $this->belongsToMany(
            PersonOfInterestVaultBeneficiary::class,
            'person_of_interest_vault_post_beneficiaries',
            'vault_post_id',
            'vault_beneficiary_id',
        )
            ->using(PersonOfInterestVaultPostBeneficiary::class)
            ->withTimestamps();
    }
}
