<?php

namespace Database\Factories;

use App\Models\ServiceProvider;
use App\Models\ServiceProviderImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceProviderImage>
 */
class ServiceProviderImageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'service_provider_id' => ServiceProvider::factory(),
            'image_path' => 'service-providers/images/'.fake()->uuid().'.jpg',
            'caption' => fake()->optional()->sentence(),
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }
}
