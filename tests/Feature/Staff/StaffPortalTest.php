<?php

use App\Enums\StaffRole;
use App\Models\MarketingAdvert;
use App\Models\StaffUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createStaffUser(StaffRole $role = StaffRole::Admin): User
{
    $user = User::factory()->create();

    StaffUser::factory()->create([
        'user_id' => $user->id,
        'role' => $role,
        'is_active' => true,
    ]);

    return $user->fresh(['staffUser']);
}

test('guests are redirected from the staff portal', function () {
    $this->get(route('staff.dashboard'))
        ->assertRedirect(route('login'));
});

test('authenticated non-staff users cannot access the staff portal', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('staff.dashboard'))
        ->assertForbidden();
});

test('inactive staff users cannot access the staff portal', function () {
    $user = User::factory()->create();

    StaffUser::factory()->inactive()->create([
        'user_id' => $user->id,
        'role' => StaffRole::Admin,
    ]);

    $this->actingAs($user)
        ->get(route('staff.dashboard'))
        ->assertForbidden();
});

test('staff admins can access the staff dashboard', function () {
    $user = createStaffUser();

    $this->actingAs($user)
        ->get(route('staff.dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('counts')
            ->has('staffUser'));
});

test('staff admins can create marketing adverts with unique codes and qr paths', function () {
    $user = createStaffUser();

    $this->actingAs($user)
        ->post(route('staff.marketing.adverts.store'), [
            'client_name' => 'Funeral Home A',
            'destination_url' => 'https://bigtreegarden.test',
            'utm_source' => 'qr',
            'utm_campaign' => 'spring-2026',
            'status' => 'active',
        ])
        ->assertRedirect();

    $advert = MarketingAdvert::query()->first();

    expect($advert)->not->toBeNull()
        ->and($advert->code)->toHaveLength(8)
        ->and($advert->qr_code_path)->toContain('api.qrserver.com')
        ->and($advert->staff_user_id)->toBe($user->staffUser->id);
});

test('marketing staff cannot access commerce routes', function () {
    $user = createStaffUser(StaffRole::Marketing);

    $this->actingAs($user)
        ->get(route('staff.commerce.transactions.index'))
        ->assertForbidden();
});
