<?php

namespace App\Enums;

use App\Enums\Concerns\ProvidesSelectOptions;

enum CrmRelationshipKind: string
{
    use ProvidesSelectOptions;

    case StrategicPartner = 'strategic_partner';
    case FulfilmentPartner = 'fulfilment_partner';
    case Institution = 'institution';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::StrategicPartner => 'Strategic partner',
            self::FulfilmentPartner => 'Fulfilment partner',
            self::Institution => 'Institution',
            self::Other => 'Other',
        };
    }
}
