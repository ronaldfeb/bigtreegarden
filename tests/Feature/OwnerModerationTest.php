<?php

use App\Enums\TransactionStatus;
use App\Models\MemorialPage;
use App\Models\MemorialPageMessage;
use App\Models\PersonOfInterest;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * @return array{owner: User, memorialPage: MemorialPage}
 */
function ownerWithMemorialPage(): array
{
    $owner = User::factory()->create();
    $personOfInterest = PersonOfInterest::factory()
        ->hasAttached($owner, ['role' => 'owner'])
        ->create();
    $memorialPage = MemorialPage::factory()->create([
        'person_of_interest_id' => $personOfInterest->id,
    ]);

    return ['owner' => $owner, 'memorialPage' => $memorialPage];
}

test('the dashboard lists pending messages for owned persons of interest only', function () {
    ['owner' => $owner, 'memorialPage' => $memorialPage] = ownerWithMemorialPage();

    $ownMessage = MemorialPageMessage::factory()->create([
        'memorial_page_id' => $memorialPage->id,
        'status' => 'pending',
    ]);

    // Message on someone else's memorial page must not be listed.
    MemorialPageMessage::factory()->create(['status' => 'pending']);

    // Non-pending messages on the owned page must not be listed either.
    MemorialPageMessage::factory()->create([
        'memorial_page_id' => $memorialPage->id,
        'status' => 'approved',
    ]);

    $this->actingAs($owner)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('user/Dashboard')
            ->has('pendingMessages', 1)
            ->where('pendingMessages.0.id', $ownMessage->id));
});

test('pending messages include transaction completion state', function () {
    ['owner' => $owner, 'memorialPage' => $memorialPage] = ownerWithMemorialPage();

    $transaction = Transaction::factory()->create([
        'status' => TransactionStatus::Complete,
    ]);

    MemorialPageMessage::factory()->create([
        'memorial_page_id' => $memorialPage->id,
        'transaction_id' => $transaction->id,
        'status' => 'pending',
        'context' => 'flowers',
    ]);

    $this->actingAs($owner)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('pendingMessages', 1)
            ->where('pendingMessages.0.transaction_complete', true)
            ->where('pendingMessages.0.context', 'flowers'));
});

test('owners can approve a message from the dashboard route', function () {
    ['owner' => $owner, 'memorialPage' => $memorialPage] = ownerWithMemorialPage();

    $message = MemorialPageMessage::factory()->create([
        'memorial_page_id' => $memorialPage->id,
        'status' => 'pending',
    ]);

    $this->actingAs($owner)
        ->post(route('messages.approve', $message))
        ->assertRedirect();

    $message->refresh();

    expect($message->status)->toBe('approved')
        ->and($message->approved_by_user_id)->toBe($owner->id)
        ->and($message->approved_at)->not->toBeNull();
});

test('owners can reject a message', function () {
    ['owner' => $owner, 'memorialPage' => $memorialPage] = ownerWithMemorialPage();

    $message = MemorialPageMessage::factory()->create([
        'memorial_page_id' => $memorialPage->id,
        'status' => 'pending',
    ]);

    $this->actingAs($owner)
        ->post(route('messages.reject', $message))
        ->assertRedirect();

    expect($message->refresh()->status)->toBe('rejected');
});
