<?php

namespace App\Enums;

use App\Enums\Concerns\ProvidesSelectOptions;

enum CrmInteractionType: string
{
    use ProvidesSelectOptions;

    case Call = 'call';
    case Email = 'email';
    case Meeting = 'meeting';
    case SiteVisit = 'site_visit';
    case Training = 'training';
    case Note = 'note';

    public function label(): string
    {
        return match ($this) {
            self::Call => 'Call',
            self::Email => 'Email',
            self::Meeting => 'Meeting',
            self::SiteVisit => 'Site visit',
            self::Training => 'Training',
            self::Note => 'Note',
        };
    }
}
