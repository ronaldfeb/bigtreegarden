<?php

use App\Enums\PamphletStatus;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\MemorialPage;
use App\Models\MemorialPageMessage;
use App\Models\MemorialPagePamphlet;
use App\Models\MemorialSite;
use App\Models\Transaction;
use App\Models\User;
use App\Services\PayfastService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    config()->set('services.payfast.merchant_id', '10000100');
    config()->set('services.payfast.merchant_key', '46f0cd694581a');
    config()->set('services.payfast.passphrase', 'test-passphrase');
    config()->set('services.payfast.url', 'https://sandbox.payfast.co.za/eng/process');
});

function createPublishedFlowerMemorialPage(): MemorialPage
{
    $pamphlet = MemorialPagePamphlet::factory()->create([
        'status' => PamphletStatus::Published,
    ]);

    return $pamphlet->memorialPage;
}

/**
 * @param  array<string, mixed>  $data
 * @return array<string, mixed>
 */
function signFlowerItnPayload(array $data): array
{
    $payfastService = app(PayfastService::class);
    $paramString = $payfastService->buildItnParameterString($data);
    $passphrase = config('services.payfast.passphrase');
    $tempParamString = $paramString;

    if (is_string($passphrase) && $passphrase !== '') {
        $tempParamString .= '&passphrase='.urlencode($passphrase);
    }

    $data['signature'] = md5($tempParamString);

    return $data;
}

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function baseFlowerItnPayload(Transaction $transaction, array $overrides = []): array
{
    return array_merge([
        'm_payment_id' => $transaction->merchant_reference,
        'pf_payment_id' => '2089250',
        'payment_status' => 'COMPLETE',
        'item_name' => 'Memorial flowers message',
        'amount_gross' => number_format($transaction->amount_cents / 100, 2, '.', ''),
        'amount_fee' => '-0.50',
        'amount_net' => number_format(($transaction->amount_cents / 100) - 0.50, 2, '.', ''),
        'merchant_id' => config('services.payfast.merchant_id'),
    ], $overrides);
}

it('creates a pending message and transaction inside the geofence and redirects to checkout', function () {
    $memorialPage = createPublishedFlowerMemorialPage();

    $site = MemorialSite::factory()->create([
        'person_of_interest_id' => $memorialPage->person_of_interest_id,
        'latitude' => -26.2041000,
        'longitude' => 28.0473000,
        'geofence_radius_m' => 50,
    ]);

    $author = User::factory()->create();

    $response = $this->actingAs($author)->post(route('memorial.flowers.store', $memorialPage->public_slug), [
        'body' => 'Rest in peace. We miss you dearly.',
        'latitude' => -26.20411,
        'longitude' => 28.04731,
    ]);

    $message = MemorialPageMessage::query()->first();

    expect($message)->not->toBeNull();
    $response->assertRedirect(route('flowers.checkout', $message));

    expect($message->context)->toBe('flowers');
    expect($message->status)->toBe('pending');
    expect($message->is_gps_verified)->toBeTrue();
    expect($message->memorial_site_id)->toBe($site->id);
    expect($message->author_user_id)->toBe($author->id);

    $transaction = Transaction::query()->first();

    expect($transaction)->not->toBeNull();
    expect($transaction->type)->toBe(TransactionType::FlowerMessage);
    expect($transaction->status)->toBe(TransactionStatus::Initiated);
    expect($transaction->amount_cents)->toBe(config('memorial.flower_price_cents'));
    expect($transaction->payable_id)->toBe($message->id);
    expect($message->transaction_id)->toBe($transaction->id);
});

it('rejects flowers posted outside all geofences without creating anything', function () {
    $memorialPage = createPublishedFlowerMemorialPage();

    MemorialSite::factory()->create([
        'person_of_interest_id' => $memorialPage->person_of_interest_id,
        'latitude' => -26.2041000,
        'longitude' => 28.0473000,
        'geofence_radius_m' => 50,
    ]);

    $author = User::factory()->create();

    $response = $this->actingAs($author)->post(route('memorial.flowers.store', $memorialPage->public_slug), [
        'body' => 'Thinking of you.',
        'latitude' => -33.9249,
        'longitude' => 18.4241,
    ]);

    $response->assertSessionHasErrors('location');

    expect(MemorialPageMessage::query()->count())->toBe(0);
    expect(Transaction::query()->count())->toBe(0);
});

it('renders the flower checkout page for the author only', function () {
    $memorialPage = createPublishedFlowerMemorialPage();
    $author = User::factory()->create();

    $message = MemorialPageMessage::factory()->create([
        'memorial_page_id' => $memorialPage->id,
        'author_user_id' => $author->id,
        'context' => 'flowers',
        'status' => 'pending',
    ]);

    $this->actingAs($author)
        ->get(route('flowers.checkout', $message))
        ->assertInertia(fn (Assert $page) => $page
            ->component('payments/FlowerCheckout')
            ->has('checkoutUrl')
            ->has('payload.signature')
        );

    $this->actingAs(User::factory()->create())
        ->get(route('flowers.checkout', $message))
        ->assertForbidden();
});

it('completes the transaction on a valid ITN while keeping the message pending', function () {
    Http::fake([
        'sandbox.payfast.co.za/eng/query/validate' => Http::response('VALID'),
    ]);

    $memorialPage = createPublishedFlowerMemorialPage();
    $author = User::factory()->create();

    $message = MemorialPageMessage::factory()->create([
        'memorial_page_id' => $memorialPage->id,
        'author_user_id' => $author->id,
        'context' => 'flowers',
        'status' => 'pending',
    ]);

    $transaction = Transaction::factory()->create([
        'payable_type' => MemorialPageMessage::class,
        'payable_id' => $message->id,
        'user_id' => $author->id,
        'type' => TransactionType::FlowerMessage,
        'status' => TransactionStatus::Initiated,
        'amount_cents' => config('memorial.flower_price_cents'),
    ]);

    $message->update(['transaction_id' => $transaction->id]);

    $payload = signFlowerItnPayload(baseFlowerItnPayload($transaction));

    $response = $this->post(route('flowers.notify', $message), $payload);

    $response->assertOk();

    $transaction->refresh();
    $message->refresh();

    expect($transaction->status)->toBe(TransactionStatus::Complete);
    expect($transaction->paid_at)->not->toBeNull();
    expect($transaction->provider_payment_id)->toBe('2089250');
    expect($message->status)->toBe('pending');
});

it('does not complete the transaction when the ITN signature is invalid', function () {
    Http::fake([
        'sandbox.payfast.co.za/eng/query/validate' => Http::response('VALID'),
    ]);

    $memorialPage = createPublishedFlowerMemorialPage();
    $author = User::factory()->create();

    $message = MemorialPageMessage::factory()->create([
        'memorial_page_id' => $memorialPage->id,
        'author_user_id' => $author->id,
        'context' => 'flowers',
        'status' => 'pending',
    ]);

    $transaction = Transaction::factory()->create([
        'payable_type' => MemorialPageMessage::class,
        'payable_id' => $message->id,
        'user_id' => $author->id,
        'type' => TransactionType::FlowerMessage,
        'status' => TransactionStatus::Initiated,
        'amount_cents' => config('memorial.flower_price_cents'),
    ]);

    $payload = baseFlowerItnPayload($transaction);
    $payload['signature'] = 'invalid-signature';

    $this->post(route('flowers.notify', $message), $payload)->assertOk();

    expect($transaction->refresh()->status)->toBe(TransactionStatus::Initiated);
});

it('hides paid flowers from the public memorial page until approved', function () {
    $memorialPage = createPublishedFlowerMemorialPage();
    $author = User::factory()->create();

    $message = MemorialPageMessage::factory()->create([
        'memorial_page_id' => $memorialPage->id,
        'author_user_id' => $author->id,
        'context' => 'flowers',
        'status' => 'pending',
    ]);

    $transaction = Transaction::factory()->create([
        'payable_type' => MemorialPageMessage::class,
        'payable_id' => $message->id,
        'user_id' => $author->id,
        'type' => TransactionType::FlowerMessage,
        'status' => TransactionStatus::Complete,
        'amount_cents' => config('memorial.flower_price_cents'),
        'paid_at' => now(),
    ]);

    $message->update(['transaction_id' => $transaction->id]);

    $this->get(route('memorial.public.show', $memorialPage->public_slug))
        ->assertInertia(fn (Assert $page) => $page
            ->component('memorial/PublicShow')
            ->count('pamphlet.flower_messages', 0)
        );
});

it('shows approved and paid flowers on the public memorial page', function () {
    $memorialPage = createPublishedFlowerMemorialPage();
    $author = User::factory()->create(['name' => 'Thandi Mokoena']);

    $message = MemorialPageMessage::factory()->create([
        'memorial_page_id' => $memorialPage->id,
        'author_user_id' => $author->id,
        'context' => 'flowers',
        'status' => 'approved',
        'body' => 'Forever in our hearts.',
        'approved_at' => now(),
    ]);

    $transaction = Transaction::factory()->create([
        'payable_type' => MemorialPageMessage::class,
        'payable_id' => $message->id,
        'user_id' => $author->id,
        'type' => TransactionType::FlowerMessage,
        'status' => TransactionStatus::Complete,
        'amount_cents' => config('memorial.flower_price_cents'),
        'paid_at' => now(),
    ]);

    $message->update(['transaction_id' => $transaction->id]);

    $this->get(route('memorial.public.show', $memorialPage->public_slug))
        ->assertInertia(fn (Assert $page) => $page
            ->component('memorial/PublicShow')
            ->count('pamphlet.flower_messages', 1)
            ->where('pamphlet.flower_messages.0.author_name', 'Thandi Mokoena')
            ->where('pamphlet.flower_messages.0.body', 'Forever in our hearts.')
        );
});

it('hides approved flowers whose transaction is not complete', function () {
    $memorialPage = createPublishedFlowerMemorialPage();
    $author = User::factory()->create();

    $message = MemorialPageMessage::factory()->create([
        'memorial_page_id' => $memorialPage->id,
        'author_user_id' => $author->id,
        'context' => 'flowers',
        'status' => 'approved',
        'approved_at' => now(),
    ]);

    $transaction = Transaction::factory()->create([
        'payable_type' => MemorialPageMessage::class,
        'payable_id' => $message->id,
        'user_id' => $author->id,
        'type' => TransactionType::FlowerMessage,
        'status' => TransactionStatus::Initiated,
        'amount_cents' => config('memorial.flower_price_cents'),
    ]);

    $message->update(['transaction_id' => $transaction->id]);

    $this->get(route('memorial.public.show', $memorialPage->public_slug))
        ->assertInertia(fn (Assert $page) => $page
            ->count('pamphlet.flower_messages', 0)
        );
});

it('cancels the transaction and removes the unpaid message when payment is cancelled', function () {
    $memorialPage = createPublishedFlowerMemorialPage();
    $author = User::factory()->create();

    $message = MemorialPageMessage::factory()->create([
        'memorial_page_id' => $memorialPage->id,
        'author_user_id' => $author->id,
        'context' => 'flowers',
        'status' => 'pending',
    ]);

    $transaction = Transaction::factory()->create([
        'payable_type' => MemorialPageMessage::class,
        'payable_id' => $message->id,
        'user_id' => $author->id,
        'type' => TransactionType::FlowerMessage,
        'status' => TransactionStatus::Initiated,
        'amount_cents' => config('memorial.flower_price_cents'),
    ]);

    $message->update(['transaction_id' => $transaction->id]);

    $response = $this->actingAs($author)->get(route('flowers.cancelled', $message));

    $response->assertRedirect(route('memorial.public.show', $memorialPage->public_slug));

    expect($transaction->refresh()->status)->toBe(TransactionStatus::Cancelled);
    expect(MemorialPageMessage::query()->find($message->id))->toBeNull();
});
