<?php

namespace Database\Factories;

use App\Models\Pamphlet;
use App\Models\PamphletStyle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PamphletStyle>
 */
class PamphletStyleFactory extends Factory
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
            'font_family' => 'Georgia',
            'is_bold' => false,
            'is_italic' => false,
            'date_format' => 'd M Y',
        ];
    }
}
