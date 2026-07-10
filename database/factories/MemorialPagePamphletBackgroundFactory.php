<?php

namespace Database\Factories;

use App\Models\MemorialPagePamphletBackground;
use App\Models\MemorialPagePamphletBackgroundCollection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MemorialPagePamphletBackground>
 */
class MemorialPagePamphletBackgroundFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'collection_id' => MemorialPagePamphletBackgroundCollection::factory(),
            'name' => fake()->words(3, true),
            'image_path' => 'assets/banners/example-'.fake()->unique()->numberBetween(1, 999).'.webp',
            'thumbnail_path' => null,
            'sort_order' => fake()->numberBetween(0, 100),
            'is_active' => true,
        ];
    }
}
