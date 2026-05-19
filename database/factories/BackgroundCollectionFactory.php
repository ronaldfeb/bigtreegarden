<?php

namespace Database\Factories;

use App\Models\BackgroundCollection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BackgroundCollection>
 */
class BackgroundCollectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'slug' => $this->faker->unique()->randomElement(['kids', 'teens', 'adults']).'-'.$this->faker->numberBetween(1, 999),
            'name' => $this->faker->words(2, true),
            'description' => $this->faker->sentence(),
        ];
    }
}
