<?php

namespace App\Models;

use Database\Factories\PersonOfInterestVaultPostBeneficiaryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

#[Fillable([
    'vault_post_id',
    'vault_beneficiary_id',
])]
class PersonOfInterestVaultPostBeneficiary extends Pivot
{
    /** @use HasFactory<PersonOfInterestVaultPostBeneficiaryFactory> */
    use HasFactory, HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $table = 'person_of_interest_vault_post_beneficiaries';

    /**
     * The pivot table uses a composite primary key and has no UUID column.
     *
     * @return array<int, string>
     */
    public function uniqueIds(): array
    {
        return [];
    }

    public function vaultPost(): BelongsTo
    {
        return $this->belongsTo(PersonOfInterestVaultPost::class, 'vault_post_id');
    }

    public function vaultBeneficiary(): BelongsTo
    {
        return $this->belongsTo(PersonOfInterestVaultBeneficiary::class, 'vault_beneficiary_id');
    }
}
