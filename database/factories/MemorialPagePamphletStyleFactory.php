<?php

namespace Database\Factories;

use App\Models\MemorialPagePamphlet;
use App\Models\MemorialPagePamphletStyle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MemorialPagePamphletStyle>
 */
class MemorialPagePamphletStyleFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'pamphlet_id' => MemorialPagePamphlet::factory()->withoutStyle(),
            'font_family' => fake()->optional()->randomElement(['serif', 'sans-serif', 'cursive']),
            'is_bold' => fake()->boolean(),
            'is_italic' => fake()->boolean(),
            'date_format' => 'd M Y',
            'text_color' => fake()->optional()->hexColor(),
        ];
    }
}
