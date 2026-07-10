<?php

namespace Database\Factories;

use App\Models\ServiceProvider;
use App\Models\ServiceProviderSpeciality;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceProviderSpeciality>
 */
class ServiceProviderSpecialityFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'service_provider_id' => ServiceProvider::factory(),
            'name' => fake()->randomElement(['Cremations', 'Traditional burials', 'Memorial services']),
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }
}
