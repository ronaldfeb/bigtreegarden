<?php

use App\Enums\ServiceProviderRole;
use App\Mail\ServiceProviderTeamAddedMail;
use App\Mail\ServiceProviderTeamInvitationMail;
use App\Models\ServiceProvider;
use App\Models\ServiceProviderInvitation;
use App\Models\ServiceProviderUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

function providerOwner(): array
{
    $serviceProvider = ServiceProvider::factory()->create();
    $owner = User::factory()->create();
    ServiceProviderUser::factory()->owner()->create([
        'service_provider_id' => $serviceProvider->id,
        'user_id' => $owner->id,
    ]);

    return [$serviceProvider, $owner];
}

test('owners can invite an existing user without a parlour', function () {
    Mail::fake();
    [$serviceProvider, $owner] = providerOwner();
    $invitee = User::factory()->create(['email' => 'staff@example.test']);

    $this->actingAs($owner)
        ->post(route('provider.team.store'), [
            'email' => 'staff@example.test',
            'role' => ServiceProviderRole::Staff->value,
        ])
        ->assertRedirect();

    expect(ServiceProviderUser::query()
        ->where('service_provider_id', $serviceProvider->id)
        ->where('user_id', $invitee->id)
        ->where('role', ServiceProviderRole::Staff)
        ->exists())->toBeTrue();

    Mail::assertSent(ServiceProviderTeamAddedMail::class);
});

test('owners can invite a new email and create a pending invitation', function () {
    Mail::fake();
    [$serviceProvider, $owner] = providerOwner();

    $this->actingAs($owner)
        ->post(route('provider.team.store'), [
            'email' => 'newhire@example.test',
            'role' => ServiceProviderRole::Staff->value,
        ])
        ->assertRedirect();

    expect(ServiceProviderInvitation::query()
        ->where('service_provider_id', $serviceProvider->id)
        ->where('email', 'newhire@example.test')
        ->exists())->toBeTrue();

    Mail::assertSent(ServiceProviderTeamInvitationMail::class);
});

test('users already on a parlour cannot be invited again', function () {
    [$serviceProvider, $owner] = providerOwner();
    $otherProvider = ServiceProvider::factory()->create();
    $takenUser = User::factory()->create(['email' => 'taken@example.test']);
    ServiceProviderUser::factory()->create([
        'service_provider_id' => $otherProvider->id,
        'user_id' => $takenUser->id,
    ]);

    $this->actingAs($owner)
        ->post(route('provider.team.store'), [
            'email' => 'taken@example.test',
            'role' => ServiceProviderRole::Staff->value,
        ])
        ->assertSessionHasErrors('email');
});

test('the last owner cannot be removed', function () {
    [$serviceProvider, $owner] = providerOwner();

    $this->actingAs($owner)
        ->delete(route('provider.team.destroy', $owner->id))
        ->assertSessionHasErrors('user');

    expect(ServiceProviderUser::query()
        ->where('service_provider_id', $serviceProvider->id)
        ->where('user_id', $owner->id)
        ->exists())->toBeTrue();
});

test('owners can view the team page', function () {
    [, $owner] = providerOwner();

    $this->actingAs($owner)
        ->get(route('provider.team.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('provider/Team')
            ->has('members', 1)
            ->has('invitations')
            ->has('roles'));
});

test('staff members cannot manage the team', function () {
    $serviceProvider = ServiceProvider::factory()->create();
    $staff = User::factory()->create();
    ServiceProviderUser::factory()->create([
        'service_provider_id' => $serviceProvider->id,
        'user_id' => $staff->id,
        'role' => ServiceProviderRole::Staff,
    ]);

    $this->actingAs($staff)
        ->get(route('provider.team.index'))
        ->assertForbidden();
});

test('new invitees can accept an invitation and join the team', function () {
    [$serviceProvider, $owner] = providerOwner();
    $plainToken = 'accept-me-token-12345678901234567890123456789012';

    ServiceProviderInvitation::factory()->create([
        'service_provider_id' => $serviceProvider->id,
        'invited_by_user_id' => $owner->id,
        'email' => 'newbie@example.test',
        'role' => ServiceProviderRole::Staff,
        'token_hash' => hash('sha256', $plainToken),
        'expires_at' => now()->addDay(),
    ]);

    $this->post(route('provider-invitations.store', $plainToken), [
        'name' => 'New Hire',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirect(route('provider.dashboard'));

    $this->assertAuthenticated();

    expect(ServiceProviderUser::query()
        ->where('service_provider_id', $serviceProvider->id)
        ->whereHas('user', fn ($query) => $query->where('email', 'newbie@example.test'))
        ->exists())->toBeTrue();
});
