<?php

namespace App\Support;

use App\Models\SubscriptionPackage;

class MemorialPackageSession
{
    public const KEY = 'memorial_package_slug';

    public static function put(mixed $slug): void
    {
        if (! is_string($slug) || $slug === '') {
            return;
        }

        $package = SubscriptionPackage::query()
            ->active()
            ->where('billing_interval', 'once_off')
            ->where('slug', $slug)
            ->first();

        if ($package === null) {
            return;
        }

        session([self::KEY => $package->slug]);
    }

    public static function slug(): ?string
    {
        $slug = session(self::KEY);

        return is_string($slug) && $slug !== '' ? $slug : null;
    }
}
