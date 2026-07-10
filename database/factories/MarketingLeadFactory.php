<?php

namespace Database\Factories;

use App\Models\MarketingLead;
use App\Models\StaffUser;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MarketingLead>
 */
class MarketingLeadFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'staff_user_id' => StaffUser::factory(),
            'marketing_advert_id' => null,
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->optional()->phoneNumber(),
            'organisation' => fake()->optional()->company(),
            'source' => fake()->randomElement(['advert', 'website', 'referral', 'walk_in', 'other']),
            'status' => 'new',
        ];
    }
}
