<?php

namespace Database\Factories;

use App\Models\PersonOfInterest;
use App\Models\User;
use App\Models\UserPersonOfInterest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserPersonOfInterest>
 */
class UserPersonOfInterestFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'person_of_interest_id' => PersonOfInterest::factory(),
            'role' => 'manager',
        ];
    }
}
