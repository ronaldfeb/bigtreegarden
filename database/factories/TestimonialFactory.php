<?php

namespace Database\Factories;

use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Testimonial>
 */
class TestimonialFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => null,
            'name' => fake()->name(),
            'photo_path' => 'testimonials/'.fake()->uuid().'.jpg',
            'role_or_location' => fake()->optional()->city(),
            'body' => fake()->paragraph(),
            'rating' => fake()->optional()->numberBetween(1, 5),
            'is_featured' => false,
            'sort_order' => fake()->numberBetween(0, 100),
            'status' => 'published',
        ];
    }

    public function forUser(): static
    {
        return $this->state(fn (array $attributes): array => [
            'user_id' => User::factory(),
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_featured' => true,
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => config('constants.testimonial.status.draft'),
        ]);
    }
}
