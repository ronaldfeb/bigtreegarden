<?php

namespace Database\Factories;

use App\Models\MemorialSite;
use App\Models\PersonOfInterest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MemorialSite>
 */
class MemorialSiteFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'person_of_interest_id' => PersonOfInterest::factory(),
            'name' => fake()->company().' Cemetery',
            'site_type' => fake()->randomElement(['burial', 'memorial', 'scattering', 'other']),
            'description' => fake()->optional()->sentence(),
            'address' => fake()->address(),
            'latitude' => fake()->latitude(-35, -22),
            'longitude' => fake()->longitude(16, 33),
            'geofence_radius_m' => 5,
        ];
    }
}
