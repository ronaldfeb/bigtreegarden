<?php

namespace App\Enums;

enum TransactionType: string
{
    case PamphletPurchase = 'pamphlet_purchase';
    case FlowerMessage = 'flower_message';
    case Subscription = 'subscription';
    case Vault = 'vault';
    case ProviderCreditPurchase = 'provider_credit_purchase';
}
