<?php

namespace App\Enums;

enum RegistrationIntent: string
{
    case Live = 'live';
    case FuneralMemorial = 'funeral-memorial';
    case MemorialLegacy = 'memorial-legacy';
    case LivingLegacy = 'living-legacy';

    /**
     * Canonicalize a query or form intent, including legacy aliases.
     */
    public static function tryFromInput(mixed $value): ?self
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        $canonical = match ($value) {
            'pamphlet' => self::FuneralMemorial->value,
            'vault' => self::LivingLegacy->value,
            default => $value,
        };

        return self::tryFrom($canonical);
    }

    public function onceOffPackageSlug(): ?string
    {
        return match ($this) {
            self::FuneralMemorial => 'funeral-memorial',
            self::MemorialLegacy => 'memorial-legacy',
            default => null,
        };
    }
}
