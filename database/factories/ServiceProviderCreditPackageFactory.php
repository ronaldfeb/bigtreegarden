<?php

namespace Database\Factories;

use App\Models\ServiceProviderCreditPackage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ServiceProviderCreditPackage>
 */
class ServiceProviderCreditPackageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('###'),
            'description' => fake()->optional()->sentence(),
            'page_count' => fake()->randomElement([5, 10, 25, 50]),
            'price_cents' => fake()->numberBetween(250000, 2500000),
            'currency' => 'ZAR',
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }
}
