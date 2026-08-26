<?php

namespace App\Enums;

enum ServiceProviderRole: string
{
    case Owner = 'owner';
    case Staff = 'staff';
}
