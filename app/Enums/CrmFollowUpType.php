<?php

namespace App\Enums;

use App\Enums\Concerns\ProvidesSelectOptions;

enum CrmFollowUpType: string
{
    use ProvidesSelectOptions;

    case FollowUp = 'follow_up';
    case Anniversary = 'anniversary';
    case Renewal = 'renewal';
    case Upgrade = 'upgrade';
    case AnnualReview = 'annual_review';

    public function label(): string
    {
        return match ($this) {
            self::FollowUp => 'Follow-up',
            self::Anniversary => 'Anniversary reminder',
            self::Renewal => 'Renewal opportunity',
            self::Upgrade => 'Upgrade opportunity',
            self::AnnualReview => 'Annual review',
        };
    }
}
