<?php

namespace Database\Factories;

use App\Models\MemorialGalleryImage;
use App\Models\MemorialPage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MemorialGalleryImage>
 */
class MemorialGalleryImageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'memorial_page_id' => MemorialPage::factory(),
            'image_path' => 'memorial/gallery/'.$this->faker->uuid().'.jpg',
            'caption' => $this->faker->sentence(),
            'sort_order' => $this->faker->numberBetween(0, 10),
        ];
    }
}
