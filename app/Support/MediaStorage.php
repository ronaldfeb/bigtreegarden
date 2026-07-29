<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use RuntimeException;

class MediaStorage
{
    public static function disk(): string
    {
        return (string) config('filesystems.media', 'public');
    }

    public static function driver(): string
    {
        return (string) config('filesystems.disks.'.self::disk().'.driver', 'local');
    }

    public static function usesLocalDriver(): bool
    {
        return self::driver() === 'local';
    }

    public static function url(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        $path = ltrim($path, '/');

        // Local public disk only — relative paths work across Sail/Vite/tunnel hosts.
        // On Laravel Cloud, the "public" disk is remapped to object storage (s3), so use the disk URL.
        if (self::usesLocalDriver()) {
            return '/storage/'.$path;
        }

        return Storage::disk(self::disk())->url($path);
    }

    public static function assertNotLocalInProduction(): void
    {
        if (! app()->isProduction()) {
            return;
        }

        if (! self::usesLocalDriver()) {
            return;
        }

        throw new RuntimeException(
            'Production media storage must use object storage (S3), not a local disk. '.
            'Set FILESYSTEM_DISK/MEDIA_DISK to your object storage disk, or ensure the Laravel Cloud public bucket disk is available.'
        );
    }
}
