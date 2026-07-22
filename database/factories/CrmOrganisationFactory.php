<?php

namespace Database\Factories;

use App\Enums\CrmOrganisationType;
use App\Enums\CrmPartnerStage;
use App\Enums\CrmRelationshipKind;
use App\Enums\CrmRelationshipStatus;
use App\Models\CrmOrganisation;
use App\Models\StaffUser;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CrmOrganisation>
 */
class CrmOrganisationFactory extends Factory
{
    protected $model = CrmOrganisation::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'type' => fake()->randomElement(CrmOrganisationType::cases()),
            'relationship_kind' => fake()->randomElement(CrmRelationshipKind::cases()),
            'partner_stage' => fake()->randomElement(CrmPartnerStage::cases()),
            'relationship_status' => fake()->randomElement(CrmRelationshipStatus::cases()),
            'relationship_owner_staff_user_id' => StaffUser::factory(),
            'service_provider_id' => null,
            'email' => fake()->companyEmail(),
            'phone' => fake()->optional()->phoneNumber(),
            'website_url' => fake()->optional()->url(),
            'geographic_coverage' => fake()->optional()->city(),
            'services_offered' => fake()->optional()->sentence(),
            'notes' => fake()->optional()->paragraph(),
            'last_contacted_at' => fake()->optional()->dateTimeBetween('-3 months'),
            'next_review_at' => fake()->optional()->dateTimeBetween('now', '+1 year'),
        ];
    }
}
