<?php

use App\Enums\CrmFollowUpType;
use App\Enums\StaffRole;
use App\Mail\CrmFollowUpDigestMail;
use App\Models\CrmContact;
use App\Models\CrmFollowUp;
use App\Models\CrmOrganisation;
use App\Models\StaffUser;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

test('the digest command emails staff with due follow-ups only', function () {
    Mail::fake();

    $withDue = StaffUser::factory()->create(['role' => StaffRole::Marketing]);
    $withoutDue = StaffUser::factory()->create(['role' => StaffRole::Marketing]);

    $organisation = CrmOrganisation::factory()->create();

    CrmFollowUp::factory()->forSubject($organisation)->overdue()->create([
        'assigned_staff_user_id' => $withDue->id,
    ]);

    CrmFollowUp::factory()->forSubject($organisation)->create([
        'assigned_staff_user_id' => $withoutDue->id,
        'due_at' => now()->addWeeks(2),
    ]);

    $this->artisan('crm:send-follow-up-digests')->assertSuccessful();

    Mail::assertSent(CrmFollowUpDigestMail::class, 1);
    Mail::assertSent(CrmFollowUpDigestMail::class, fn (CrmFollowUpDigestMail $mail) => $mail->hasTo($withDue->user->email));
    Mail::assertNotSent(function (CrmFollowUpDigestMail $mail) use ($withoutDue) {
        return $mail->hasTo($withoutDue->user->email);
    });
});

test('renewal follow-ups are generated for contacts with upcoming subscription renewals', function () {
    $owner = StaffUser::factory()->create();
    $user = User::factory()->create();

    Subscription::factory()->active()->create([
        'user_id' => $user->id,
        'next_billing_at' => now()->addDays(10),
    ]);

    $contact = CrmContact::factory()->create([
        'user_id' => $user->id,
        'relationship_owner_staff_user_id' => $owner->id,
    ]);

    $this->artisan('crm:generate-renewal-follow-ups')->assertSuccessful();

    expect($contact->followUps()->where('type', CrmFollowUpType::Renewal)->count())->toBe(1);

    $this->artisan('crm:generate-renewal-follow-ups')->assertSuccessful();

    expect($contact->followUps()->where('type', CrmFollowUpType::Renewal)->count())->toBe(1);
});
