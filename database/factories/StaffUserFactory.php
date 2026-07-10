<?php

namespace Database\Factories;

use App\Enums\StaffRole;
use App\Models\StaffUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StaffUser>
 */
class StaffUserFactory extends Factory
{
    protected $model = StaffUser::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'role' => StaffRole::Admin,
            'job_title' => fake()->jobTitle(),
            'is_active' => true,
            'invited_by_staff_user_id' => null,
            'last_active_at' => null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }

    public function marketing(): static
    {
        return $this->state(fn (array $attributes): array => [
            'role' => StaffRole::Marketing,
        ]);
    }
}
