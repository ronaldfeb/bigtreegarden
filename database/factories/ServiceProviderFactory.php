<?php

namespace Database\Factories;

use App\Models\ServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceProvider>
 */
class ServiceProviderFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'slug' => fake()->unique()->slug(),
            'registration_number' => fake()->optional()->numerify('####/######/##'),
            'vat_number' => null,
            'description' => fake()->optional()->paragraphs(2, true),
            'logo_path' => null,
            'cover_image_path' => null,
            'email' => fake()->companyEmail(),
            'phone' => fake()->optional()->phoneNumber(),
            'website_url' => fake()->optional()->url(),
            'physical_address' => fake()->optional()->address(),
            'city' => fake()->city(),
            'province' => fake()->state(),
            'status' => 'pending',
            'credits_remaining' => 0,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => config('constants.service_provider.status.active'),
        ]);
    }

    public function withCredits(int $credits = 10): static
    {
        return $this->state(fn (array $attributes): array => [
            'credits_remaining' => $credits,
        ]);
    }
}
