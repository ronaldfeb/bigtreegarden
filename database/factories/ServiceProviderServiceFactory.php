<?php

namespace Database\Factories;

use App\Models\ServiceProvider;
use App\Models\ServiceProviderService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceProviderService>
 */
class ServiceProviderServiceFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'service_provider_id' => ServiceProvider::factory(),
            'name' => fake()->words(3, true),
            'description' => fake()->optional()->sentence(),
            'price_from_cents' => fake()->optional()->numberBetween(50000, 500000),
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }
}
