<?php

namespace Database\Factories;

use App\Models\Pamphlet;
use App\Models\PamphletQrCode;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PamphletQrCode>
 */
class PamphletQrCodeFactory extends Factory
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
            'target_url' => 'https://example.test/memorial/'.$this->faker->slug(),
            'image_path' => 'https://api.qrserver.com/v1/create-qr-code/?size=320x320&data=example',
            'generated_at' => now(),
        ];
    }
}
