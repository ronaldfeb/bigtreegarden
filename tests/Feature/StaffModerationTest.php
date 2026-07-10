<?php

use App\Enums\StaffRole;
use App\Models\MemorialPage;
use App\Models\MemorialPageMessage;
use App\Models\PersonOfInterest;
use App\Models\StaffUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function moderationStaffUser(StaffRole $role = StaffRole::Admin): User
{
    $user = User::factory()->create();

    StaffUser::factory()->create([
        'user_id' => $user->id,
        'role' => $role,
        'is_active' => true,
    ]);

    return $user->fresh(['staffUser']);
}

test('staff can approve a pending message', function () {
    $staff = moderationStaffUser();
    $message = MemorialPageMessage::factory()->create();

    $this->actingAs($staff)
        ->post(route('messages.approve', $message))
        ->assertRedirect();

    $message->refresh();

    expect($message->status)->toBe('approved')
        ->and($message->approved_by_user_id)->toBe($staff->id)
        ->and($message->approved_at)->not->toBeNull();
});

test('staff can reject a pending message', function () {
    $staff = moderationStaffUser();
    $message = MemorialPageMessage::factory()->create();

    $this->actingAs($staff)
        ->post(route('messages.reject', $message))
        ->assertRedirect();

    expect($message->refresh()->status)->toBe('rejected');
});

test('users unrelated to the memorial page cannot moderate messages', function () {
    $user = User::factory()->create();
    $message = MemorialPageMessage::factory()->create();

    $this->actingAs($user)
        ->post(route('messages.approve', $message))
        ->assertForbidden();

    $this->actingAs($user)
        ->post(route('messages.reject', $message))
        ->assertForbidden();

    expect($message->refresh()->status)->toBe('pending');
});

test('memorial page owners can approve messages', function () {
    $owner = User::factory()->create();
    $personOfInterest = PersonOfInterest::factory()
        ->hasAttached($owner, ['role' => 'owner'])
        ->create();
    $memorialPage = MemorialPage::factory()->create([
        'person_of_interest_id' => $personOfInterest->id,
    ]);
    $message = MemorialPageMessage::factory()->create([
        'memorial_page_id' => $memorialPage->id,
    ]);

    $this->actingAs($owner)
        ->post(route('messages.approve', $message))
        ->assertRedirect();

    $message->refresh();

    expect($message->status)->toBe('approved')
        ->and($message->approved_by_user_id)->toBe($owner->id);
});

test('staff can view the memorial page message moderation index with filters', function () {
    $staff = moderationStaffUser();

    MemorialPageMessage::factory()->create([
        'status' => 'pending',
        'context' => 'flowers',
    ]);
    MemorialPageMessage::factory()->create([
        'status' => 'approved',
        'context' => 'live_day',
    ]);

    $this->actingAs($staff)
        ->get(route('staff.directory.memorial-page-messages.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('staff/directory/memorial-page-messages/Index')
            ->has('messages.data', 2));

    $this->actingAs($staff)
        ->get(route('staff.directory.memorial-page-messages.index', ['status' => 'pending']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('messages.data', 1)
            ->where('messages.data.0.status', 'pending')
            ->where('filters.status', 'pending'));

    $this->actingAs($staff)
        ->get(route('staff.directory.memorial-page-messages.index', ['context' => 'live_day']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('messages.data', 1)
            ->where('messages.data.0.context', 'live_day'));
});

test('non-staff users cannot view the moderation index', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('staff.directory.memorial-page-messages.index'))
        ->assertForbidden();
});
