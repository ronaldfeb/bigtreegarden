<?php

namespace Database\Factories;

use App\Enums\ServiceProviderRole;
use App\Models\ServiceProvider;
use App\Models\ServiceProviderUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceProviderUser>
 */
class ServiceProviderUserFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'service_provider_id' => ServiceProvider::factory(),
            'user_id' => User::factory(),
            'role' => ServiceProviderRole::Staff,
        ];
    }

    public function owner(): static
    {
        return $this->state(fn (array $attributes): array => [
            'role' => ServiceProviderRole::Owner,
        ]);
    }
}
