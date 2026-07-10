<?php

namespace App\Enums;

enum MemorialPageStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Archived = 'archived';
}
