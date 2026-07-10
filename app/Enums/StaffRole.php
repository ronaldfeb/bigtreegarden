<?php

namespace App\Enums;

enum StaffRole: string
{
    case Admin = 'admin';
    case Marketing = 'marketing';
    case Content = 'content';
    case Support = 'support';
}
