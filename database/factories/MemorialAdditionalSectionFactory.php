<?php

namespace Database\Factories;

use App\Models\MemorialAdditionalSection;
use App\Models\MemorialPage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MemorialAdditionalSection>
 */
class MemorialAdditionalSectionFactory extends Factory
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
            'title' => $this->faker->sentence(3),
            'content' => $this->faker->paragraph(),
            'sort_order' => $this->faker->numberBetween(0, 10),
        ];
    }
}
