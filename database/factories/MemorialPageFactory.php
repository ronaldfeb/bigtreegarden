<?php

namespace Database\Factories;

use App\Models\MemorialPage;
use App\Models\Pamphlet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MemorialPage>
 */
class MemorialPageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'pamphlet_id' => Pamphlet::factory(),
            'funeral_programme' => $this->faker->paragraph(),
            'obituary' => $this->faker->paragraph(),
            'hymns' => $this->faker->paragraph(),
            'gallery_enabled' => true,
        ];
    }
}
