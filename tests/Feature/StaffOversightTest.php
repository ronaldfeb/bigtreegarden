<?php

use App\Enums\StaffRole;
use App\Models\PersonOfInterestVault;
use App\Models\PersonOfInterestVaultBeneficiary;
use App\Models\PersonOfInterestVaultMedia;
use App\Models\PersonOfInterestVaultPost;
use App\Models\ServiceProvider;
use App\Models\StaffUser;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function oversightStaffUser(StaffRole $role = StaffRole::Admin): User
{
    $user = User::factory()->create();

    StaffUser::factory()->create([
        'user_id' => $user->id,
        'role' => $role,
        'is_active' => true,
    ]);

    return $user->fresh(['staffUser']);
}

test('staff can view the vault oversight index with usage stats', function () {
    $staff = oversightStaffUser();

    $vault = PersonOfInterestVault::factory()->create();
    PersonOfInterestVaultBeneficiary::factory()->count(2)->create(['vault_id' => $vault->id]);
    PersonOfInterestVaultMedia::factory()->count(2)->create([
        'vault_id' => $vault->id,
        'file_size_bytes' => 1048576,
    ]);
    PersonOfInterestVaultPost::factory()->create(['vault_id' => $vault->id]);

    $this->actingAs($staff)
        ->get(route('staff.directory.vaults.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('staff/directory/vaults/Index')
            ->has('vaults.data', 1)
            ->where('vaults.data.0.beneficiaries_count', 2)
            ->where('vaults.data.0.media_count', 2)
            ->where('vaults.data.0.posts_count', 1)
            ->where('vaults.data.0.storage_used_mb', 2)
            ->where('vaults.data.0.status', 'sealed'));
});

test('non-staff users cannot view the vault oversight index', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('staff.directory.vaults.index'))
        ->assertForbidden();
});

test('staff can view the subscriptions index with a status filter', function () {
    $staff = oversightStaffUser();

    Subscription::factory()->active()->create();
    Subscription::factory()->cancelled()->create();

    $this->actingAs($staff)
        ->get(route('staff.commerce.subscriptions.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('staff/commerce/subscriptions/Index')
            ->has('subscriptions.data', 2));

    $this->actingAs($staff)
        ->get(route('staff.commerce.subscriptions.index', ['status' => 'active']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('subscriptions.data', 1)
            ->where('subscriptions.data.0.status', 'active')
            ->where('filters.status', 'active'));
});

test('staff can approve a pending service provider', function () {
    $staff = oversightStaffUser();
    $serviceProvider = ServiceProvider::factory()->create();

    expect($serviceProvider->status)->toBe('pending');

    $this->actingAs($staff)
        ->post(route('staff.directory.service-providers.approve', $serviceProvider))
        ->assertRedirect();

    expect($serviceProvider->refresh()->status)->toBe('active');
});

test('staff cannot approve a service provider that is not pending', function () {
    $staff = oversightStaffUser();
    $serviceProvider = ServiceProvider::factory()->active()->create();

    $this->actingAs($staff)
        ->post(route('staff.directory.service-providers.approve', $serviceProvider))
        ->assertStatus(422);

    expect($serviceProvider->refresh()->status)->toBe('active');
});

test('staff can suspend a service provider', function () {
    $staff = oversightStaffUser();
    $serviceProvider = ServiceProvider::factory()->active()->create();

    $this->actingAs($staff)
        ->post(route('staff.directory.service-providers.suspend', $serviceProvider))
        ->assertRedirect();

    expect($serviceProvider->refresh()->status)->toBe('suspended');
});
