<?php

namespace App\Enums;

use App\Enums\Concerns\ProvidesSelectOptions;

enum CrmPartnerStage: string
{
    use ProvidesSelectOptions;

    case InitialEngagement = 'initial_engagement';
    case Assessment = 'assessment';
    case Approval = 'approval';
    case Training = 'training';
    case ActiveReferrals = 'active_referrals';
    case RelationshipManagement = 'relationship_management';

    public function label(): string
    {
        return match ($this) {
            self::InitialEngagement => 'Initial engagement',
            self::Assessment => 'Assessment',
            self::Approval => 'Approval',
            self::Training => 'Training',
            self::ActiveReferrals => 'Active referrals',
            self::RelationshipManagement => 'Relationship management',
        };
    }

    public function order(): int
    {
        return match ($this) {
            self::InitialEngagement => 1,
            self::Assessment => 2,
            self::Approval => 3,
            self::Training => 4,
            self::ActiveReferrals => 5,
            self::RelationshipManagement => 6,
        };
    }
}
