<?php

namespace Database\Factories;

use App\Models\MarketingAdvert;
use App\Models\StaffUser;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<MarketingAdvert>
 */
class MarketingAdvertFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'staff_user_id' => StaffUser::factory(),
            'client_name' => fake()->company(),
            'campaign_name' => fake()->optional()->words(3, true),
            'code' => Str::lower(Str::random(8)),
            'destination_url' => fake()->url(),
            'utm_source' => 'qr',
            'utm_medium' => 'qr',
            'utm_campaign' => fake()->slug(),
            'utm_content' => null,
            'qr_code_path' => null,
            'status' => 'active',
            'starts_at' => null,
            'ends_at' => null,
        ];
    }
}
