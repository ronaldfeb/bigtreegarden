<?php

namespace Database\Factories;

use App\Models\MemorialPage;
use App\Models\MemorialPageImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MemorialPageImage>
 */
class MemorialPageImageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'memorial_page_id' => MemorialPage::factory(),
            'image_path' => 'memorial-pages/images/'.fake()->uuid().'.jpg',
            'caption' => fake()->optional()->sentence(),
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }
}
