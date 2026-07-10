<?php

use App\Models\MemorialPagePamphlet;
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
    $this->get(route('register', ['intent' => 'vault']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('auth/Register')
            ->where('intent', 'vault'));
});

it('ignores an invalid intent on the register page', function () {
    $this->get(route('register', ['intent' => 'bogus']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('auth/Register')
            ->where('intent', null));
});

it('redirects to pamphlet creation after registering with the pamphlet intent', function () {
    $response = $this->post(route('register.store'), registrationPayload([
        'intent' => 'pamphlet',
    ]));

    $this->assertAuthenticated();
    $response->assertRedirect(route('pamphlets.create'));
});

it('redirects to pricing after registering with the vault intent', function () {
    $response = $this->post(route('register.store'), registrationPayload([
        'intent' => 'vault',
    ]));

    $this->assertAuthenticated();
    $response->assertRedirect(route('pricing'));
    $response->assertSessionHas('status');
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
            'intent' => 'vault',
        ]));

    $this->assertAuthenticated();
    $response->assertRedirect($intendedUrl);
});
