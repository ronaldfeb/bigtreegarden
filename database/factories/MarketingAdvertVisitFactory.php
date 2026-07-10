<?php

namespace Database\Factories;

use App\Models\MarketingAdvert;
use App\Models\MarketingAdvertVisit;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<MarketingAdvertVisit>
 */
class MarketingAdvertVisitFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'marketing_advert_id' => MarketingAdvert::factory(),
            'visitor_hash' => Str::random(64),
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'referrer' => fake()->optional()->url(),
            'visited_at' => now(),
        ];
    }
}
