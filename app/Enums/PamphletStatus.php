<?php

namespace App\Enums;

enum PamphletStatus: string
{
    case Draft = 'draft';
    case PendingPayment = 'pending_payment';
    case Paid = 'paid';
    case Published = 'published';
}
