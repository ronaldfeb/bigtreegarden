<?php

namespace Database\Factories;

use App\Models\ServiceProvider;
use App\Models\ServiceProviderBackground;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceProviderBackground>
 */
class ServiceProviderBackgroundFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'service_provider_id' => ServiceProvider::factory(),
            'name' => fake()->words(3, true),
            'image_path' => 'providers/backgrounds/'.fake()->uuid().'.jpg',
            'sort_order' => 0,
        ];
    }
}
