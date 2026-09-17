<?php

use App\Models\User;
use App\Services\IntercomJwtService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function decodeIntercomJwtPayload(string $jwt): array
{
    $parts = explode('.', $jwt);

    expect($parts)->toHaveCount(3);

    $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true, 512, JSON_THROW_ON_ERROR);

    expect($payload)->toBeArray();

    return $payload;
}

it('returns null when messenger security is not configured', function () {
    config()->set('services.intercom.messenger_secret', null);

    $user = User::factory()->create();
    $service = new IntercomJwtService;

    expect($service->forUser($user))->toBeNull();
    expect($service->forUser(null))->toBeNull();
});

it('generates a signed jwt for authenticated users', function () {
    config()->set('services.intercom.messenger_secret', 'test-messenger-secret');
    config()->set('services.intercom.jwt_ttl', 3600);

    $user = User::factory()->create([
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
    ]);

    $service = new IntercomJwtService;
    $jwt = $service->forUser($user);

    expect($jwt)->toBeString()->not->toBeEmpty();

    $payload = decodeIntercomJwtPayload($jwt);

    expect($payload['user_id'])->toBe((string) $user->id)
        ->and($payload['email'])->toBe('jane@example.com')
        ->and($payload['name'])->toBe('Jane Doe')
        ->and($payload['created_at'])->toBe($user->created_at?->getTimestamp())
        ->and($payload['exp'])->toBeGreaterThan(now()->getTimestamp());

    $parts = explode('.', $jwt);
    $expectedSignature = rtrim(strtr(base64_encode(hash_hmac(
        'sha256',
        $parts[0].'.'.$parts[1],
        'test-messenger-secret',
        true,
    )), '+/', '-_'), '=');

    expect($parts[2])->toBe($expectedSignature);
});

it('shares intercom user jwt with inertia for authenticated users', function () {
    config()->set('services.intercom.messenger_secret', 'test-messenger-secret');

    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page->where('intercomUserJwt', fn ($jwt) => is_string($jwt) && $jwt !== ''));
});
