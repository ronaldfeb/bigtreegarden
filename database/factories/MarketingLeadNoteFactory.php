<?php

namespace Database\Factories;

use App\Models\MarketingLead;
use App\Models\MarketingLeadNote;
use App\Models\StaffUser;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MarketingLeadNote>
 */
class MarketingLeadNoteFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'marketing_lead_id' => MarketingLead::factory(),
            'staff_user_id' => StaffUser::factory(),
            'body' => fake()->paragraph(),
        ];
    }
}
