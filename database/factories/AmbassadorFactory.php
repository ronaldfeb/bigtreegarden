<?php

namespace Database\Factories;

use App\Models\Ambassador;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ambassador>
 */
class AmbassadorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'title' => fake()->jobTitle(),
            'description' => fake()->optional()->paragraphs(2, true),
            'image_url' => 'https://placehold.net/avatar.svg',
            'status' => config('constants.ambassador.status.active'),
            'handle_linkedin' => null,
            'handle_facebook' => null,
            'handle_instagram' => null,
            'website_url' => null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => config('constants.ambassador.status.inactive'),
        ]);
    }

    public function withSocials(): static
    {
        return $this->state(fn (array $attributes): array => [
            'handle_linkedin' => 'https://linkedin.com/in/'.fake()->userName(),
            'handle_facebook' => 'https://facebook.com/'.fake()->userName(),
            'handle_instagram' => 'https://instagram.com/'.fake()->userName(),
            'website_url' => fake()->url(),
        ]);
    }
}
