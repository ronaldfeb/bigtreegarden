<?php

namespace Database\Factories;

use App\Models\MemorialPage;
use App\Models\MemorialPageSection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MemorialPageSection>
 */
class MemorialPageSectionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'memorial_page_id' => MemorialPage::factory(),
            'title' => fake()->sentence(3),
            'body' => fake()->paragraphs(2, true),
            'sort_order' => fake()->numberBetween(0, 100),
            'is_visible' => true,
        ];
    }
}
