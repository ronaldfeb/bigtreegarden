<?php

namespace Database\Factories;

use App\Models\MemorialPagePamphletBackgroundCollection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MemorialPagePamphletBackgroundCollection>
 */
class MemorialPagePamphletBackgroundCollectionFactory extends Factory
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
            'sort_order' => fake()->numberBetween(0, 100),
            'is_active' => true,
        ];
    }
}
