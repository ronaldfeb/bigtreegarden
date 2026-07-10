<?php

namespace Database\Factories;

use App\Models\HelpCenterCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HelpCenterCategory>
 */
class HelpCenterCategoryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'slug' => fake()->unique()->slug(),
            'sort_order' => fake()->numberBetween(0, 100),
            'is_active' => true,
        ];
    }
}
