<?php

namespace App\Enums;

use App\Enums\Concerns\ProvidesSelectOptions;

enum CrmLifecycleStage: string
{
    use ProvidesSelectOptions;

    case Enquiry = 'enquiry';
    case LegacyProfile = 'legacy_profile';
    case PackageSelected = 'package_selected';
    case BespokeQuote = 'bespoke_quote';
    case AdditionalServices = 'additional_services';
    case Subscriber = 'subscriber';
    case FamilyUpdates = 'family_updates';
    case Referrer = 'referrer';

    public function label(): string
    {
        return match ($this) {
            self::Enquiry => 'Initial enquiry',
            self::LegacyProfile => 'Legacy profile',
            self::PackageSelected => 'Common package selection',
            self::BespokeQuote => 'Bespoke quotation',
            self::AdditionalServices => 'Additional services',
            self::Subscriber => 'Subscription services',
            self::FamilyUpdates => 'Family updates',
            self::Referrer => 'Referral activity',
        };
    }

    public function order(): int
    {
        return match ($this) {
            self::Enquiry => 1,
            self::LegacyProfile => 2,
            self::PackageSelected => 3,
            self::BespokeQuote => 4,
            self::AdditionalServices => 5,
            self::Subscriber => 6,
            self::FamilyUpdates => 7,
            self::Referrer => 8,
        };
    }
}
