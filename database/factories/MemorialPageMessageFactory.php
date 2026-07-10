<?php

namespace Database\Factories;

use App\Models\MemorialPage;
use App\Models\MemorialPageMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MemorialPageMessage>
 */
class MemorialPageMessageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'memorial_page_id' => MemorialPage::factory(),
            'author_user_id' => User::factory(),
            'memorial_site_id' => null,
            'transaction_id' => null,
            'type' => 'text',
            'body' => fake()->paragraph(),
            'image_path' => null,
            'context' => fake()->randomElement(['live_day', 'flowers']),
            'posted_latitude' => null,
            'posted_longitude' => null,
            'is_gps_verified' => false,
            'status' => 'pending',
            'approved_by_user_id' => null,
            'approved_at' => null,
        ];
    }
}
