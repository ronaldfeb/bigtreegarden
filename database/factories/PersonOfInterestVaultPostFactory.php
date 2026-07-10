<?php

namespace Database\Factories;

use App\Models\PersonOfInterestVault;
use App\Models\PersonOfInterestVaultPost;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PersonOfInterestVaultPost>
 */
class PersonOfInterestVaultPostFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'vault_id' => PersonOfInterestVault::factory(),
            'author_user_id' => User::factory(),
            'title' => fake()->optional()->sentence(3),
            'body' => fake()->paragraphs(2, true),
            'visibility' => 'all',
        ];
    }
}
