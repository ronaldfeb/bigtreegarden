<?php

namespace Database\Factories;

use App\Models\HelpCenterTopic;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HelpCenterTopic>
 */
class HelpCenterTopicFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'slug' => fake()->unique()->slug(),
            'description' => fake()->optional()->sentence(),
            'icon' => fake()->optional()->randomElement(['book', 'help-circle', 'life-buoy']),
            'sort_order' => fake()->numberBetween(0, 100),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }
}
