<?php

namespace App\Enums;

use App\Enums\Concerns\ProvidesSelectOptions;

enum CrmRelationshipStatus: string
{
    use ProvidesSelectOptions;

    case Prospect = 'prospect';
    case Active = 'active';
    case Dormant = 'dormant';
    case Ended = 'ended';

    public function label(): string
    {
        return match ($this) {
            self::Prospect => 'Prospect',
            self::Active => 'Active',
            self::Dormant => 'Dormant',
            self::Ended => 'Ended',
        };
    }
}
