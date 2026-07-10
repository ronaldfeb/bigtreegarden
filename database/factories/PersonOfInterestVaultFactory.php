<?php

namespace Database\Factories;

use App\Models\PersonOfInterest;
use App\Models\PersonOfInterestVault;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PersonOfInterestVault>
 */
class PersonOfInterestVaultFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'person_of_interest_id' => PersonOfInterest::factory(),
            'name' => fake()->optional()->words(3, true),
            'status' => 'sealed',
            'released_at' => null,
            'storage_limit_mb' => 1024,
        ];
    }

    public function released(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'released',
            'released_at' => now(),
        ]);
    }
}
