<?php

namespace Database\Factories;

use App\Enums\PersonOfInterestStatus;
use App\Models\PersonOfInterest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PersonOfInterest>
 */
class PersonOfInterestFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();

        return [
            'created_by_user_id' => User::factory(),
            'guest_token_hash' => null,
            'guest_token_expires_at' => null,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'display_name' => "{$firstName} {$lastName}",
            'date_of_birth' => fake()->dateTimeBetween('-70 years', '-20 years'),
            'date_of_passing' => fake()->dateTimeBetween('-5 years', 'now'),
            'place_of_birth' => fake()->city(),
            'place_of_passing' => fake()->city(),
            'profile_image_path' => null,
            'public_slug' => Str::lower((string) Str::ulid()),
            'qr_code_path' => null,
            'qr_generated_at' => null,
            'status' => PersonOfInterestStatus::Draft,
        ];
    }
}
