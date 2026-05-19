<?php

namespace Database\Factories;

use App\Models\Background;
use App\Models\BackgroundCollection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Background>
 */
class BackgroundFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'background_collection_id' => BackgroundCollection::factory(),
            'name' => $this->faker->words(3, true),
            'asset_path' => 'assets/banners/example-'.$this->faker->unique()->numberBetween(1, 999).'.webp',
            'thumbnail_path' => null,
            'is_active' => true,
            'sort_order' => $this->faker->numberBetween(0, 100),
        ];
    }
}
