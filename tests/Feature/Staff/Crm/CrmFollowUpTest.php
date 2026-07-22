<?php

use App\Enums\CrmFollowUpStatus;
use App\Enums\CrmFollowUpType;
use App\Enums\StaffRole;
use App\Models\CrmFollowUp;
use App\Models\CrmOrganisation;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('a follow-up can be scheduled against an organisation', function () {
    $user = makeStaffUser(StaffRole::Marketing);
    $organisation = CrmOrganisation::factory()->create();

    $this->actingAs($user)
        ->post(route('staff.crm.follow-ups.store'), [
            'subject_type' => 'organisation',
            'subject_id' => $organisation->id,
            'type' => CrmFollowUpType::FollowUp->value,
            'title' => 'Send proposal',
            'due_at' => now()->addWeek()->toDateTimeString(),
        ])
        ->assertRedirect(route('staff.crm.organisations.show', $organisation));

    expect($organisation->followUps()->count())->toBe(1);
});

test('completing a follow-up marks it done and logs an interaction', function () {
    $user = makeStaffUser(StaffRole::Admin);
    $organisation = CrmOrganisation::factory()->create();
    $followUp = CrmFollowUp::factory()->forSubject($organisation)->create();

    $this->actingAs($user)
        ->post(route('staff.crm.follow-ups.complete', $followUp))
        ->assertRedirect();

    $followUp->refresh();

    expect($followUp->status)->toBe(CrmFollowUpStatus::Done)
        ->and($followUp->completed_at)->not->toBeNull()
        ->and($organisation->interactions()->count())->toBe(1);
});

test('cancelling a follow-up marks it cancelled', function () {
    $user = makeStaffUser(StaffRole::Admin);
    $followUp = CrmFollowUp::factory()->forSubject(CrmOrganisation::factory()->create())->create();

    $this->actingAs($user)
        ->post(route('staff.crm.follow-ups.cancel', $followUp))
        ->assertRedirect();

    expect($followUp->fresh()->status)->toBe(CrmFollowUpStatus::Cancelled);
});

test('the follow-ups index defaults to pending items', function () {
    $user = makeStaffUser(StaffRole::Marketing);
    $organisation = CrmOrganisation::factory()->create();

    CrmFollowUp::factory()->forSubject($organisation)->overdue()->create();
    CrmFollowUp::factory()->forSubject($organisation)->done()->create();

    $this->actingAs($user)
        ->get(route('staff.crm.follow-ups.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('staff/crm/follow-ups/Index')
            ->has('followUps.data', 1));
});
