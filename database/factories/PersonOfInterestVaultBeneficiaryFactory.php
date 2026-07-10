<?php

namespace Database\Factories;

use App\Models\PersonOfInterestVault;
use App\Models\PersonOfInterestVaultBeneficiary;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<PersonOfInterestVaultBeneficiary>
 */
class PersonOfInterestVaultBeneficiaryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'vault_id' => PersonOfInterestVault::factory(),
            'type' => 'beneficiary',
            'full_name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'contact_number' => fake()->phoneNumber(),
            'physical_address' => fake()->address(),
            'access_code_hash' => Hash::make('1234'),
            'access_code_hint' => '1234',
            'first_accessed_at' => null,
        ];
    }

    public function executor(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => 'executor',
        ]);
    }

    public function withAccessCode(string $plainCode): static
    {
        return $this->state(fn (array $attributes): array => [
            'access_code_hash' => Hash::make($plainCode),
            'access_code_hint' => substr($plainCode, 0, 2).'******'.substr($plainCode, -2),
        ]);
    }
}
