<?php

namespace Database\Factories;

use App\Enums\CrmJourney;
use App\Enums\CrmLifecycleStage;
use App\Models\CrmContact;
use App\Models\StaffUser;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CrmContact>
 */
class CrmContactFactory extends Factory
{
    protected $model = CrmContact::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'crm_organisation_id' => null,
            'relationship_owner_staff_user_id' => StaffUser::factory(),
            'user_id' => null,
            'marketing_lead_id' => null,
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->optional()->phoneNumber(),
            'role_title' => fake()->optional()->jobTitle(),
            'journey' => fake()->randomElement(CrmJourney::cases()),
            'lifecycle_stage' => fake()->randomElement(CrmLifecycleStage::cases()),
            'notes' => fake()->optional()->paragraph(),
            'last_contacted_at' => fake()->optional()->dateTimeBetween('-3 months'),
        ];
    }
}
