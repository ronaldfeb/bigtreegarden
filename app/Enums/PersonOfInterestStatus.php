<?php

namespace App\Enums;

enum PersonOfInterestStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Archived = 'archived';
}
