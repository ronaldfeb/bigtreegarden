<?php

namespace Database\Seeders;

use App\Enums\CrmFollowUpStatus;
use App\Enums\CrmFollowUpType;
use App\Enums\CrmInteractionType;
use App\Enums\CrmJourney;
use App\Enums\CrmLifecycleStage;
use App\Enums\CrmOrganisationType;
use App\Enums\CrmPartnerStage;
use App\Enums\CrmRelationshipKind;
use App\Enums\CrmRelationshipStatus;
use App\Models\CrmContact;
use App\Models\CrmOrganisation;
use App\Models\ServiceProvider;
use App\Models\StaffUser;
use App\Models\User;
use Illuminate\Database\Seeder;

class CrmDemoSeeder extends Seeder
{
    public function run(): void
    {
        $owner = StaffUser::query()
            ->whereHas('user', fn ($query) => $query->where('email', 'staff@bigtreegarden.test'))
            ->first();

        if ($owner === null) {
            return;
        }

        $serviceProvider = ServiceProvider::query()->where('slug', 'sunrise-funeral-services')->first();

        $parlour = CrmOrganisation::query()->updateOrCreate(
            ['name' => 'Sunrise Funeral Services'],
            [
                'type' => CrmOrganisationType::FuneralParlour,
                'relationship_kind' => CrmRelationshipKind::FulfilmentPartner,
                'partner_stage' => CrmPartnerStage::ActiveReferrals,
                'relationship_status' => CrmRelationshipStatus::Active,
                'relationship_owner_staff_user_id' => $owner->id,
                'service_provider_id' => $serviceProvider?->id,
                'email' => 'partnerships@sunrisefunerals.test',
                'phone' => '+27 11 555 0202',
                'geographic_coverage' => 'Gauteng',
                'services_offered' => 'Funerals, cremations, memorial coordination.',
                'last_contacted_at' => now()->subWeeks(2),
                'next_review_at' => now()->addMonths(10),
            ],
        );

        $church = CrmOrganisation::query()->updateOrCreate(
            ['name' => 'St Georges Cathedral'],
            [
                'type' => CrmOrganisationType::Church,
                'relationship_kind' => CrmRelationshipKind::StrategicPartner,
                'partner_stage' => CrmPartnerStage::Assessment,
                'relationship_status' => CrmRelationshipStatus::Prospect,
                'relationship_owner_staff_user_id' => $owner->id,
                'email' => 'office@stgeorges.test',
                'geographic_coverage' => 'Western Cape',
                'last_contacted_at' => now()->subMonths(2),
            ],
        );

        CrmOrganisation::query()->updateOrCreate(
            ['name' => 'National Heritage Museum'],
            [
                'type' => CrmOrganisationType::Museum,
                'relationship_kind' => CrmRelationshipKind::Institution,
                'partner_stage' => CrmPartnerStage::InitialEngagement,
                'relationship_status' => CrmRelationshipStatus::Prospect,
                'relationship_owner_staff_user_id' => $owner->id,
                'geographic_coverage' => 'National',
            ],
        );

        $customer = User::query()->where('email', 'memorial@bigtreegarden.test')->first();

        $parlourContact = CrmContact::query()->updateOrCreate(
            ['name' => 'Thandiwe Nkosi', 'crm_organisation_id' => $parlour->id],
            [
                'relationship_owner_staff_user_id' => $owner->id,
                'email' => 'thandiwe@sunrisefunerals.test',
                'phone' => '+27 82 555 0110',
                'role_title' => 'Partnerships Manager',
                'journey' => CrmJourney::InstitutionalHeritage,
                'lifecycle_stage' => CrmLifecycleStage::AdditionalServices,
                'last_contacted_at' => now()->subWeeks(2),
            ],
        );

        $legacyContact = CrmContact::query()->updateOrCreate(
            ['name' => 'Memorial Owner', 'user_id' => $customer?->id],
            [
                'relationship_owner_staff_user_id' => $owner->id,
                'email' => 'memorial@bigtreegarden.test',
                'journey' => CrmJourney::LegacyPreservation,
                'lifecycle_stage' => CrmLifecycleStage::Subscriber,
                'last_contacted_at' => now()->subDays(5),
            ],
        );

        $parlour->interactions()->firstOrCreate(
            ['summary' => 'Quarterly partnership review'],
            [
                'staff_user_id' => $owner->id,
                'type' => CrmInteractionType::Meeting,
                'body' => 'Reviewed referral volumes and agreed on co-branded pamphlet templates.',
                'occurred_at' => now()->subWeeks(2),
            ],
        );

        $church->interactions()->firstOrCreate(
            ['summary' => 'Introductory call'],
            [
                'staff_user_id' => $owner->id,
                'type' => CrmInteractionType::Call,
                'body' => 'Introduced BTG memorial offering to the parish office.',
                'occurred_at' => now()->subMonths(2),
            ],
        );

        $parlour->followUps()->firstOrCreate(
            ['title' => 'Send updated referral agreement'],
            [
                'assigned_staff_user_id' => $owner->id,
                'type' => CrmFollowUpType::FollowUp,
                'status' => CrmFollowUpStatus::Pending,
                'due_at' => now()->subDays(2),
            ],
        );

        $church->followUps()->firstOrCreate(
            ['title' => 'Follow up on partnership proposal'],
            [
                'assigned_staff_user_id' => $owner->id,
                'type' => CrmFollowUpType::FollowUp,
                'status' => CrmFollowUpStatus::Pending,
                'due_at' => now()->addDays(5),
            ],
        );

        $parlourContact->followUps()->firstOrCreate(
            ['title' => 'Confirm training session date'],
            [
                'assigned_staff_user_id' => $owner->id,
                'type' => CrmFollowUpType::FollowUp,
                'status' => CrmFollowUpStatus::Pending,
                'due_at' => now()->addWeek(),
            ],
        );

        $legacyContact->followUps()->firstOrCreate(
            ['title' => 'Subscription renewal check-in'],
            [
                'assigned_staff_user_id' => $owner->id,
                'type' => CrmFollowUpType::Renewal,
                'status' => CrmFollowUpStatus::Pending,
                'due_at' => now()->addDays(20),
            ],
        );
    }
}
