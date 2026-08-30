<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Features;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->skipUnlessFortifyFeature(Features::registration());
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('new users can register', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('registration rejects a missing password confirmation field', function () {
    $this->from(route('register', ['intent' => 'funeral-memorial']))
        ->post(route('register.store'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'intent' => 'funeral-memorial',
        ])
        ->assertSessionHasErrors('password');

    $this->assertGuest();
});

test('the register form posts confirmation as password_confirmation', function () {
    $register = file_get_contents(resource_path('js/pages/auth/Register.vue'));

    expect($register)
        ->toContain('id="password_confirmation"')
        ->toContain('name="password_confirmation"')
        ->not->toMatch('/id="password_confirmation"[\s\S]*?name="password"/');
});
