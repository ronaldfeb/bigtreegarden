<?php

namespace Database\Factories;

use App\Models\Policy;
use App\Models\StaffUser;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Policy>
 */
class PolicyFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => fake()->unique()->randomElement(['terms_of_service', 'privacy_policy', 'about_us']),
            'title' => fake()->sentence(3),
            'body' => fake()->paragraphs(3, true),
            'version' => 'v1.0',
            'published_at' => now(),
            'updated_by_staff_user_id' => StaffUser::factory(),
        ];
    }
}
