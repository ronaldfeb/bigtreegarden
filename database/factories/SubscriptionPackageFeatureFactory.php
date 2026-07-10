<?php

namespace Database\Factories;

use App\Models\SubscriptionPackage;
use App\Models\SubscriptionPackageFeature;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SubscriptionPackageFeature>
 */
class SubscriptionPackageFeatureFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'subscription_package_id' => SubscriptionPackage::factory(),
            'label' => fake()->sentence(3),
            'description' => fake()->optional()->sentence(),
            'is_included' => true,
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }
}
