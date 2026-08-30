<?php

use App\Enums\SubscriptionStatus;
use App\Enums\TransactionStatus;
use App\Models\Subscription;
use App\Models\SubscriptionPackage;
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

/**
 * @param  array<string, mixed>  $data
 * @return array<string, mixed>
 */
function signSubscriptionItnPayload(array $data): array
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
function subscriptionItnPayload(Transaction $transaction, array $overrides = []): array
{
    return array_merge([
        'm_payment_id' => $transaction->merchant_reference,
        'pf_payment_id' => '2089250',
        'payment_status' => 'COMPLETE',
        'item_name' => 'Vault subscription',
        'amount_gross' => number_format($transaction->amount_cents / 100, 2, '.', ''),
        'merchant_id' => config('services.payfast.merchant_id'),
        'token' => 'pf-subscription-token-123',
    ], $overrides);
}

it('starts living legacy checkout from the dedicated start route', function () {
    $user = User::factory()->create();
    $package = SubscriptionPackage::factory()->create([
        'slug' => 'living-legacy',
        'billing_interval' => 'monthly',
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)->get(route('subscriptions.start'));

    $subscription = Subscription::query()->where('user_id', $user->id)->first();

    expect($subscription)->not->toBeNull();
    expect($subscription->subscription_package_id)->toBe($package->id);
    $response->assertRedirect(route('subscriptions.checkout', $subscription));
});

it('starts a subscription checkout from a recurring package', function () {
    $user = User::factory()->create();
    $package = SubscriptionPackage::factory()->create(['billing_interval' => 'monthly']);

    $response = $this->actingAs($user)->post(route('subscriptions.store', $package));

    $subscription = Subscription::query()->where('user_id', $user->id)->first();

    expect($subscription)->not->toBeNull();
    expect($subscription->status)->toBe(SubscriptionStatus::Pending);
    $response->assertRedirect(route('subscriptions.checkout', $subscription));
});

it('rejects subscribing to a once-off package', function () {
    $user = User::factory()->create();
    $package = SubscriptionPackage::factory()->create(['billing_interval' => 'once_off']);

    $this->actingAs($user)
        ->post(route('subscriptions.store', $package))
        ->assertNotFound();
});

it('renders vault subscription checkout with pricing props', function () {
    $package = SubscriptionPackage::factory()->create([
        'name' => 'Vault Monthly',
        'billing_interval' => 'monthly',
        'price_cents' => 9900,
        'currency' => 'ZAR',
    ]);
    $subscription = Subscription::factory()->create([
        'subscription_package_id' => $package->id,
        'status' => SubscriptionStatus::Pending,
    ]);

    $this->actingAs($subscription->user)
        ->get(route('subscriptions.checkout', $subscription))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('subscriptions/Checkout')
            ->where('packageName', 'Vault Monthly')
            ->where('amount_cents', 9900)
            ->where('currency', 'ZAR')
            ->where('billing_interval', 'monthly')
            ->where('autoSubmit', false)
            ->has('checkoutUrl')
            ->has('payload'));
});

it('builds a recurring checkout payload with subscription fields', function () {
    $package = SubscriptionPackage::factory()->create([
        'billing_interval' => 'annual',
        'price_cents' => 49900,
    ]);
    $subscription = Subscription::factory()->create(['subscription_package_id' => $package->id]);
    $transaction = Transaction::factory()->create([
        'payable_type' => Subscription::class,
        'payable_id' => $subscription->id,
        'user_id' => $subscription->user_id,
        'merchant_reference' => $subscription->merchant_reference,
        'amount_cents' => 49900,
        'status' => TransactionStatus::Initiated,
    ]);

    $payload = app(PayfastService::class)->buildSubscriptionCheckoutPayload($subscription, $transaction);

    expect($payload['subscription_type'])->toBe(1);
    expect($payload['recurring_amount'])->toBe('499.00');
    expect($payload['frequency'])->toBe(6);
    expect($payload['cycles'])->toBe(0);
    expect($payload)->toHaveKey('signature');
});

it('activates a subscription when a valid ITN is received', function () {
    Http::fake([
        'sandbox.payfast.co.za/eng/query/validate' => Http::response('VALID'),
    ]);

    $package = SubscriptionPackage::factory()->create(['billing_interval' => 'monthly', 'price_cents' => 9900]);
    $subscription = Subscription::factory()->create(['subscription_package_id' => $package->id]);
    $transaction = Transaction::factory()->create([
        'payable_type' => Subscription::class,
        'payable_id' => $subscription->id,
        'user_id' => $subscription->user_id,
        'merchant_reference' => $subscription->merchant_reference,
        'amount_cents' => 9900,
        'status' => TransactionStatus::Initiated,
    ]);

    $payload = signSubscriptionItnPayload(subscriptionItnPayload($transaction));

    $this->post(route('subscriptions.notify', $subscription), $payload)->assertOk();

    $subscription->refresh();
    $transaction->refresh();

    expect($subscription->status)->toBe(SubscriptionStatus::Active);
    expect($subscription->payfast_token)->toBe('pf-subscription-token-123');
    expect($subscription->next_billing_at)->not->toBeNull();
    expect($transaction->status)->toBe(TransactionStatus::Complete);
    expect($subscription->user->hasActiveSubscription())->toBeTrue();
});

it('records a renewal ITN as a new transaction', function () {
    Http::fake([
        'sandbox.payfast.co.za/eng/query/validate' => Http::response('VALID'),
    ]);

    $package = SubscriptionPackage::factory()->create(['billing_interval' => 'monthly', 'price_cents' => 9900]);
    $subscription = Subscription::factory()->active()->create(['subscription_package_id' => $package->id]);
    $transaction = Transaction::factory()->create([
        'payable_type' => Subscription::class,
        'payable_id' => $subscription->id,
        'user_id' => $subscription->user_id,
        'merchant_reference' => $subscription->merchant_reference,
        'amount_cents' => 9900,
        'status' => TransactionStatus::Complete,
    ]);

    $payload = signSubscriptionItnPayload(subscriptionItnPayload($transaction));

    $this->post(route('subscriptions.notify', $subscription), $payload)->assertOk();

    expect($subscription->transactions()->count())->toBe(2);
    expect(
        $subscription->transactions()->where('status', TransactionStatus::Complete)->count()
    )->toBe(2);
});

it('does not activate a subscription when the ITN signature is invalid', function () {
    Http::fake([
        'sandbox.payfast.co.za/eng/query/validate' => Http::response('VALID'),
    ]);

    $package = SubscriptionPackage::factory()->create(['billing_interval' => 'monthly', 'price_cents' => 9900]);
    $subscription = Subscription::factory()->create(['subscription_package_id' => $package->id]);
    $transaction = Transaction::factory()->create([
        'payable_type' => Subscription::class,
        'payable_id' => $subscription->id,
        'user_id' => $subscription->user_id,
        'merchant_reference' => $subscription->merchant_reference,
        'amount_cents' => 9900,
        'status' => TransactionStatus::Initiated,
    ]);

    $payload = subscriptionItnPayload($transaction);
    $payload['signature'] = 'invalid-signature';

    $this->post(route('subscriptions.notify', $subscription), $payload)->assertOk();

    expect($subscription->refresh()->status)->toBe(SubscriptionStatus::Pending);
});

it('cancels an active subscription', function () {
    $subscription = Subscription::factory()->active()->create();

    $this->actingAs($subscription->user)
        ->delete(route('subscriptions.cancel', $subscription))
        ->assertRedirect(route('subscriptions.show'));

    $subscription->refresh();

    expect($subscription->status)->toBe(SubscriptionStatus::Cancelled);
    expect($subscription->cancelled_at)->not->toBeNull();
});

it('prevents cancelling another users subscription', function () {
    $subscription = Subscription::factory()->active()->create();
    $otherUser = User::factory()->create();

    $this->actingAs($otherUser)
        ->delete(route('subscriptions.cancel', $subscription))
        ->assertForbidden();
});
