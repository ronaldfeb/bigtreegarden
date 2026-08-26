<?php

namespace App\Enums;

enum ServiceProviderCreditPurchaseStatus: string
{
    case PendingPayment = 'pending_payment';
    case PendingReview = 'pending_review';
    case Released = 'released';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';
}
