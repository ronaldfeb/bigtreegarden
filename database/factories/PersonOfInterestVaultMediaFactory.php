<?php

namespace Database\Factories;

use App\Models\PersonOfInterestVault;
use App\Models\PersonOfInterestVaultMedia;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PersonOfInterestVaultMedia>
 */
class PersonOfInterestVaultMediaFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'vault_id' => PersonOfInterestVault::factory(),
            'uploaded_by_user_id' => User::factory(),
            'type' => fake()->randomElement(['image', 'video', 'pdf', 'voice_note']),
            'title' => fake()->optional()->sentence(3),
            'file_path' => 'vault/media/'.fake()->uuid().'.jpg',
            'mime_type' => 'image/jpeg',
            'file_size_bytes' => fake()->numberBetween(1024, 10485760),
            'duration_seconds' => null,
        ];
    }
}
