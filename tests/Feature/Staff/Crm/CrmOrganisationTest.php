<?php

use App\Enums\CrmOrganisationType;
use App\Enums\CrmRelationshipKind;
use App\Enums\CrmRelationshipStatus;
use App\Enums\StaffRole;
use App\Models\CrmOrganisation;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('marketing staff can list crm organisations', function () {
    CrmOrganisation::factory()->count(2)->create();

    $this->actingAs(makeStaffUser(StaffRole::Marketing))
        ->get(route('staff.crm.organisations.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('staff/crm/organisations/Index')
            ->has('organisations.data', 2));
});

test('content staff cannot access the crm', function () {
    $this->actingAs(makeStaffUser(StaffRole::Content))
        ->get(route('staff.crm.organisations.index'))
        ->assertForbidden();
});

test('support staff cannot access the crm', function () {
    $this->actingAs(makeStaffUser(StaffRole::Support))
        ->get(route('staff.crm.organisations.index'))
        ->assertForbidden();
});

test('marketing staff can create a crm organisation', function () {
    $this->actingAs(makeStaffUser(StaffRole::Marketing))
        ->post(route('staff.crm.organisations.store'), [
            'name' => 'Sunset Chapels',
            'type' => CrmOrganisationType::FuneralParlour->value,
            'relationship_kind' => CrmRelationshipKind::StrategicPartner->value,
            'relationship_status' => CrmRelationshipStatus::Prospect->value,
        ])
        ->assertRedirect();

    expect(CrmOrganisation::query()->where('name', 'Sunset Chapels')->exists())->toBeTrue();
});

test('a crm organisation can be updated and deleted', function () {
    $user = makeStaffUser(StaffRole::Admin);
    $organisation = CrmOrganisation::factory()->create();

    $this->actingAs($user)
        ->put(route('staff.crm.organisations.update', $organisation), [
            'name' => 'Renamed Org',
            'type' => CrmOrganisationType::Church->value,
            'relationship_kind' => CrmRelationshipKind::Institution->value,
            'relationship_status' => CrmRelationshipStatus::Active->value,
        ])
        ->assertRedirect(route('staff.crm.organisations.show', $organisation));

    expect($organisation->fresh()->name)->toBe('Renamed Org');

    $this->actingAs($user)
        ->delete(route('staff.crm.organisations.destroy', $organisation))
        ->assertRedirect(route('staff.crm.organisations.index'));

    expect($organisation->fresh()->trashed())->toBeTrue();
});
