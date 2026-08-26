<?php

namespace App\Enums;

enum ServiceProviderCreditPaymentMethod: string
{
    case Payfast = 'payfast';
    case BankTransfer = 'bank_transfer';
}
