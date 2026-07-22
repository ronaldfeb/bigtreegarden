<?php

use App\Enums\CrmInteractionType;
use App\Enums\StaffRole;
use App\Models\CrmOrganisation;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('logging an interaction updates the subject last contacted timestamp', function () {
    $user = makeStaffUser(StaffRole::Admin);
    $organisation = CrmOrganisation::factory()->create(['last_contacted_at' => null]);

    $this->actingAs($user)
        ->post(route('staff.crm.interactions.store'), [
            'subject_type' => 'organisation',
            'subject_id' => $organisation->id,
            'type' => CrmInteractionType::Call->value,
            'summary' => 'Called to discuss partnership',
        ])
        ->assertRedirect(route('staff.crm.organisations.show', $organisation));

    $organisation->refresh();

    expect($organisation->interactions()->count())->toBe(1)
        ->and($organisation->last_contacted_at)->not->toBeNull();
});

test('an interaction with an unknown subject type is rejected', function () {
    $this->actingAs(makeStaffUser())
        ->post(route('staff.crm.interactions.store'), [
            'subject_type' => 'invalid',
            'subject_id' => fake()->uuid(),
            'type' => CrmInteractionType::Note->value,
            'summary' => 'Nope',
        ])
        ->assertSessionHasErrors('subject_type');
});
