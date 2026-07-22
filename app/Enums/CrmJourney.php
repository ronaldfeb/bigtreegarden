<?php

namespace App\Enums;

use App\Enums\Concerns\ProvidesSelectOptions;

enum CrmJourney: string
{
    use ProvidesSelectOptions;

    case LegacyPreservation = 'legacy_preservation';
    case FuneralPreparedness = 'funeral_preparedness';
    case BereavementSupport = 'bereavement_support';
    case FamilyHistory = 'family_history';
    case InstitutionalHeritage = 'institutional_heritage';

    public function label(): string
    {
        return match ($this) {
            self::LegacyPreservation => 'Legacy preservation',
            self::FuneralPreparedness => 'Funeral preparedness',
            self::BereavementSupport => 'Immediate bereavement support',
            self::FamilyHistory => 'Family history preservation',
            self::InstitutionalHeritage => 'Institutional heritage project',
        };
    }
}
