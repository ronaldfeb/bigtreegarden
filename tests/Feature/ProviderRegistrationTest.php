<?php

use App\Enums\ServiceProviderRole;
use App\Models\ServiceProvider;
use App\Models\ServiceProviderUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('guests can register a new funeral home', function () {
    $this->post(route('providers.register.store'), [
        'name' => 'Jane Owner',
        'email' => 'jane@parlour.test',
        'password' => 'password',
        'password_confirmation' => 'password',
        'business_name' => 'Jane Funerals',
        'registration_number' => '2020/123456/07',
        'vat_number' => '4123456789',
        'contact_phone' => '0115550000',
        'contact_email' => 'info@janefunerals.test',
    ])->assertRedirect(route('provider.dashboard'));

    $this->assertAuthenticated();

    $provider = ServiceProvider::query()->where('name', 'Jane Funerals')->first();

    expect($provider)->not->toBeNull()
        ->and($provider->status)->toBe('pending')
        ->and($provider->registration_number)->toBe('2020/123456/07')
        ->and($provider->vat_number)->toBe('4123456789')
        ->and($provider->credits_remaining)->toBe(0);

    expect(ServiceProviderUser::query()
        ->where('service_provider_id', $provider->id)
        ->where('role', ServiceProviderRole::Owner)
        ->exists())->toBeTrue();
});

test('authenticated users can register a funeral home without creating a second account', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('providers.register.store'), [
            'business_name' => 'Existing User Funerals',
            'registration_number' => '2019/111111/07',
            'contact_phone' => '0215551111',
            'contact_email' => 'hello@existing.test',
        ])
        ->assertRedirect(route('provider.dashboard'));

    expect(User::query()->count())->toBe(1);
    expect(ServiceProvider::query()->where('name', 'Existing User Funerals')->exists())->toBeTrue();
});

test('pending providers cannot buy credits or create memorials', function () {
    $serviceProvider = ServiceProvider::factory()->create(['status' => 'pending']);
    $user = User::factory()->create();
    ServiceProviderUser::factory()->owner()->create([
        'service_provider_id' => $serviceProvider->id,
        'user_id' => $user->id,
    ]);

    $this->actingAs($user)
        ->get(route('provider.credits.index'))
        ->assertForbidden();

    $this->actingAs($user)
        ->get(route('provider.memorials.index'))
        ->assertForbidden();
});

test('pending providers are not listed in the public directory', function () {
    ServiceProvider::factory()->create(['name' => 'Hidden Parlour', 'status' => 'pending']);

    $this->get(route('providers.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('providers.data', 0),
        );
});
