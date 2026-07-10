<?php

namespace Database\Factories;

use App\Models\PersonOfInterestVaultBeneficiary;
use App\Models\PersonOfInterestVaultPost;
use App\Models\PersonOfInterestVaultPostBeneficiary;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PersonOfInterestVaultPostBeneficiary>
 */
class PersonOfInterestVaultPostBeneficiaryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'vault_post_id' => PersonOfInterestVaultPost::factory(),
            'vault_beneficiary_id' => PersonOfInterestVaultBeneficiary::factory(),
        ];
    }
}
