<?php

use App\Support\MediaStorage;
use Illuminate\Support\Facades\Config;

it('builds relative urls for the local public media disk', function () {
    Config::set('filesystems.media', 'public');
    Config::set('filesystems.disks.public.driver', 'local');

    expect(MediaStorage::url('pamphlets/images/photo.jpg'))
        ->toBe('/storage/pamphlets/images/photo.jpg');
});

it('uses the cloud disk url when the media driver is s3', function () {
    Config::set('filesystems.media', 'public');
    Config::set('filesystems.disks.public', [
        'driver' => 's3',
        'key' => 'testing',
        'secret' => 'testing',
        'region' => 'us-east-1',
        'bucket' => 'btg-media',
        'url' => 'https://cdn.example.test',
        'endpoint' => null,
        'use_path_style_endpoint' => false,
        'throw' => false,
    ]);

    expect(MediaStorage::url('pamphlets/images/photo.jpg'))
        ->toBe('https://cdn.example.test/pamphlets/images/photo.jpg');
});

it('rejects a local media disk in production', function () {
    $previous = app()->environment();

    try {
        app()->detectEnvironment(fn (): string => 'production');

        Config::set('filesystems.media', 'public');
        Config::set('filesystems.disks.public.driver', 'local');

        expect(fn () => MediaStorage::assertNotLocalInProduction())
            ->toThrow(RuntimeException::class, 'Production media storage must use object storage');
    } finally {
        app()->detectEnvironment(fn (): string => $previous);
    }
});
