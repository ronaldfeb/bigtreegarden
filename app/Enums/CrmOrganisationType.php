<?php

namespace App\Enums;

use App\Enums\Concerns\ProvidesSelectOptions;

enum CrmOrganisationType: string
{
    use ProvidesSelectOptions;

    case FuneralParlour = 'funeral_parlour';
    case Church = 'church';
    case ChurchAssociation = 'church_association';
    case Museum = 'museum';
    case HeritageOrganisation = 'heritage_organisation';
    case Insurer = 'insurer';
    case CommunityOrganisation = 'community_organisation';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::FuneralParlour => 'Funeral parlour',
            self::Church => 'Church',
            self::ChurchAssociation => 'Church association',
            self::Museum => 'Museum',
            self::HeritageOrganisation => 'Heritage organisation',
            self::Insurer => 'Insurer',
            self::CommunityOrganisation => 'Community organisation',
            self::Other => 'Other',
        };
    }
}
