<?php

use App\Models\MemorialPagePamphlet;
use App\Models\Subscription;
use App\Models\SubscriptionPackage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Fortify\Features;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->skipUnlessFortifyFeature(Features::registration());
});

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function registrationPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ], $overrides);
}

it('passes a valid intent from the query string to the register page', function () {
    $this->get(route('register', ['intent' => 'living-legacy']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('auth/Register')
            ->has('packages')
            ->where('intent', 'living-legacy'));
});

it('canonicalizes the vault alias to living-legacy', function () {
    $this->get(route('register', ['intent' => 'vault']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('auth/Register')
            ->where('intent', 'living-legacy'));
});

it('canonicalizes the pamphlet alias to funeral-memorial', function () {
    $this->get(route('register', ['intent' => 'pamphlet']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('auth/Register')
            ->where('intent', 'funeral-memorial'));
});

it('ignores an invalid intent on the register page', function () {
    $this->get(route('register', ['intent' => 'bogus']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('auth/Register')
            ->where('intent', null));
});

it('redirects to pamphlet creation after registering with the funeral-memorial intent', function () {
    $response = $this->post(route('register.store'), registrationPayload([
        'intent' => 'funeral-memorial',
    ]));

    $this->assertAuthenticated();
    $response->assertRedirect(route('pamphlets.create'));
});

it('redirects to pamphlet creation after registering with the pamphlet alias', function () {
    $response = $this->post(route('register.store'), registrationPayload([
        'intent' => 'pamphlet',
    ]));

    $this->assertAuthenticated();
    $response->assertRedirect(route('pamphlets.create'));
});

it('stores the memorial-legacy package on the session after registering', function () {
    SubscriptionPackage::factory()->create([
        'slug' => 'memorial-legacy',
        'billing_interval' => 'once_off',
        'price_cents' => 149900,
        'is_active' => true,
    ]);

    $response = $this->post(route('register.store'), registrationPayload([
        'intent' => 'memorial-legacy',
    ]));

    $this->assertAuthenticated();
    $response->assertRedirect(route('pamphlets.create'));
    expect(session('memorial_package_slug'))->toBe('memorial-legacy');
});

it('starts living legacy checkout after registering with the living-legacy intent', function () {
    $package = SubscriptionPackage::factory()->create([
        'slug' => 'living-legacy',
        'billing_interval' => 'monthly',
        'is_active' => true,
    ]);

    $response = $this->post(route('register.store'), registrationPayload([
        'intent' => 'living-legacy',
    ]));

    $this->assertAuthenticated();
    $response->assertRedirect(route('subscriptions.start'));

    $follow = $this->get(route('subscriptions.start'));
    $subscription = Subscription::query()->where('user_id', auth()->id())->first();

    expect($subscription)->not->toBeNull();
    expect($subscription->subscription_package_id)->toBe($package->id);
    $follow->assertRedirect(route('subscriptions.checkout', $subscription));
});

it('starts living legacy checkout after registering with the vault alias', function () {
    SubscriptionPackage::factory()->create([
        'slug' => 'living-legacy',
        'billing_interval' => 'monthly',
        'is_active' => true,
    ]);

    $response = $this->post(route('register.store'), registrationPayload([
        'intent' => 'vault',
    ]));

    $this->assertAuthenticated();
    $response->assertRedirect(route('subscriptions.start'));
});

it('redirects to find a memorial after registering with the live intent', function () {
    $response = $this->post(route('register.store'), registrationPayload([
        'intent' => 'live',
    ]));

    $this->assertAuthenticated();
    $response->assertRedirect(route('memorial.find'));
});

it('redirects to the dashboard when registering without an intent', function () {
    $response = $this->post(route('register.store'), registrationPayload());

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

it('prefers the intended url over the intent redirect', function () {
    $pamphlet = MemorialPagePamphlet::factory()->guest()->create();
    $intendedUrl = route('pamphlets.continue', $pamphlet);

    $response = $this->withSession(['url.intended' => $intendedUrl])
        ->post(route('register.store'), registrationPayload([
            'intent' => 'living-legacy',
        ]));

    $this->assertAuthenticated();
    $response->assertRedirect($intendedUrl);
});
