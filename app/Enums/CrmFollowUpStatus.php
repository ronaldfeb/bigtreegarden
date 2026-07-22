<?php

namespace App\Enums;

use App\Enums\Concerns\ProvidesSelectOptions;

enum CrmFollowUpStatus: string
{
    use ProvidesSelectOptions;

    case Pending = 'pending';
    case Done = 'done';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Done => 'Done',
            self::Cancelled => 'Cancelled',
        };
    }
}
