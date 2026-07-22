<?php

use App\Enums\CrmLifecycleStage;
use App\Enums\StaffRole;
use App\Models\CrmContact;
use App\Models\MarketingLead;
use App\Models\MarketingLeadNote;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('marketing staff can create a crm contact', function () {
    $this->actingAs(makeStaffUser(StaffRole::Marketing))
        ->post(route('staff.crm.contacts.store'), [
            'name' => 'Jane Doe',
            'email' => 'jane@example.test',
            'lifecycle_stage' => CrmLifecycleStage::Enquiry->value,
        ])
        ->assertRedirect();

    expect(CrmContact::query()->where('name', 'Jane Doe')->exists())->toBeTrue();
});

test('a marketing lead can be converted into a crm contact with notes copied as interactions', function () {
    $user = makeStaffUser(StaffRole::Marketing);

    $lead = MarketingLead::factory()->create([
        'staff_user_id' => $user->staffUser->id,
        'name' => 'Convert Me',
        'email' => 'convert@example.test',
        'status' => 'new',
    ]);

    MarketingLeadNote::factory()->count(2)->create([
        'marketing_lead_id' => $lead->id,
        'staff_user_id' => $user->staffUser->id,
    ]);

    $this->actingAs($user)
        ->post(route('staff.marketing.leads.convert', $lead))
        ->assertRedirect();

    $contact = CrmContact::query()->where('marketing_lead_id', $lead->id)->first();

    expect($contact)->not->toBeNull()
        ->and($contact->name)->toBe('Convert Me')
        ->and($contact->lifecycle_stage)->toBe(CrmLifecycleStage::Enquiry)
        ->and($contact->interactions()->count())->toBe(2)
        ->and($lead->fresh()->status)->toBe('converted');
});

test('converting the same lead twice does not create a duplicate contact', function () {
    $user = makeStaffUser(StaffRole::Marketing);
    $lead = MarketingLead::factory()->create(['staff_user_id' => $user->staffUser->id]);

    $this->actingAs($user)->post(route('staff.marketing.leads.convert', $lead));
    $this->actingAs($user)->post(route('staff.marketing.leads.convert', $lead));

    expect(CrmContact::query()->where('marketing_lead_id', $lead->id)->count())->toBe(1);
});
