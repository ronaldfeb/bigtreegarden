<?php

namespace Database\Factories;

use App\Models\ServiceProvider;
use App\Models\ServiceProviderSocialMedia;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceProviderSocialMedia>
 */
class ServiceProviderSocialMediaFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'service_provider_id' => ServiceProvider::factory(),
            'platform' => fake()->randomElement(['facebook', 'instagram', 'tiktok', 'x', 'youtube', 'linkedin', 'whatsapp']),
            'url' => fake()->url(),
        ];
    }
}
