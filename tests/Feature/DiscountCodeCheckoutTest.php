<?php

use App\Enums\DiscountType;
use App\Enums\PamphletStatus;
use App\Enums\ServiceProviderCreditPaymentMethod;
use App\Enums\ServiceProviderCreditPurchaseStatus;
use App\Enums\SubscriptionStatus;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\DiscountCode;
use App\Models\MemorialPagePamphlet;
use App\Models\ServiceProvider;
use App\Models\ServiceProviderCreditPackage;
use App\Models\ServiceProviderCreditPurchase;
use App\Models\ServiceProviderUser;
use App\Models\Subscription;
use App\Models\SubscriptionPackage;
use App\Models\Transaction;
use App\Models\User;
use App\Services\PayfastService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

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
function signDiscountItnPayload(array $data): array
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

it('rejects expired, used, reserved, and wrong-package codes on pamphlet checkout', function () {
    $package = SubscriptionPackage::factory()->create([
        'slug' => 'funeral-memorial',
        'billing_interval' => 'once_off',
        'price_cents' => 69900,
        'is_active' => true,
    ]);
    $otherPackage = SubscriptionPackage::factory()->create([
        'slug' => 'memorial-legacy',
        'billing_interval' => 'once_off',
        'price_cents' => 149900,
        'is_active' => true,
    ]);
    $pamphlet = MemorialPagePamphlet::factory()->create(['status' => PamphletStatus::PendingPayment]);
    $owner = $pamphlet->owner();

    $this->actingAs($owner)->get(route('payments.checkout', $pamphlet))->assertOk();

    $expired = DiscountCode::factory()->expired()->create();
    $used = DiscountCode::factory()->used()->create();
    $wrong = DiscountCode::factory()->forPackage($otherPackage)->create();
    $reserved = DiscountCode::factory()->create();
    $otherTxn = Transaction::factory()->create(['status' => TransactionStatus::Initiated]);
    $reserved->update([
        'reserved_transaction_id' => $otherTxn->id,
        'reserved_at' => now(),
    ]);

    foreach ([$expired, $used, $wrong, $reserved] as $code) {
        $this->actingAs($owner)
            ->post(route('payments.discount.apply', $pamphlet), ['code' => $code->formattedCode()])
            ->assertSessionHasErrors('code');
    }

    expect($package->price_cents)->toBe(69900);
});

it('applies a 10 percent pamphlet discount and consumes the code on ITN', function () {
    Http::fake([
        'sandbox.payfast.co.za/eng/query/validate' => Http::response('VALID'),
    ]);

    SubscriptionPackage::factory()->create([
        'slug' => 'funeral-memorial',
        'billing_interval' => 'once_off',
        'price_cents' => 69900,
        'currency' => 'ZAR',
        'is_active' => true,
    ]);

    $pamphlet = MemorialPagePamphlet::factory()->create(['status' => PamphletStatus::PendingPayment]);
    $code = DiscountCode::factory()->create([
        'discount_type' => DiscountType::Percent,
        'percent' => 10,
        'applies_to_all' => true,
    ]);

    $this->actingAs($pamphlet->owner())->get(route('payments.checkout', $pamphlet))->assertOk();

    $this->actingAs($pamphlet->owner())
        ->post(route('payments.discount.apply', $pamphlet), ['code' => $code->formattedCode()])
        ->assertRedirect(route('payments.checkout', $pamphlet));

    $transaction = Transaction::query()->first();

    expect($transaction->amount_cents)->toBe(62910)
        ->and($transaction->original_amount_cents)->toBe(69900)
        ->and($code->fresh()->reserved_transaction_id)->toBe($transaction->id);

    $this->actingAs($pamphlet->owner())
        ->get(route('payments.checkout', $pamphlet))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('amount_cents', 62910)
            ->where('payload.amount', '629.10')
            ->where('discount.formatted_code', $code->formattedCode())
            ->where('autoSubmit', false));

    $payload = signDiscountItnPayload([
        'm_payment_id' => $transaction->merchant_reference,
        'pf_payment_id' => '1089250',
        'payment_status' => 'COMPLETE',
        'item_name' => 'Memorial pamphlet',
        'amount_gross' => '629.10',
        'amount_fee' => '-4.60',
        'amount_net' => '624.50',
        'merchant_id' => config('services.payfast.merchant_id'),
    ]);

    $this->post(route('payments.notify', $pamphlet), $payload)->assertOk();

    expect($code->fresh()->used_at)->not->toBeNull()
        ->and($code->fresh()->used_transaction_id)->toBe($transaction->id)
        ->and($pamphlet->fresh()->status)->toBe(PamphletStatus::Paid);

    $secondPamphlet = MemorialPagePamphlet::factory()->create(['status' => PamphletStatus::PendingPayment]);
    $this->actingAs($secondPamphlet->owner())->get(route('payments.checkout', $secondPamphlet));
    $this->actingAs($secondPamphlet->owner())
        ->post(route('payments.discount.apply', $secondPamphlet), ['code' => $code->formattedCode()])
        ->assertSessionHasErrors('code');
});

it('releases a reserved pamphlet discount on cancel', function () {
    SubscriptionPackage::factory()->create([
        'slug' => 'funeral-memorial',
        'billing_interval' => 'once_off',
        'price_cents' => 69900,
        'is_active' => true,
    ]);

    $pamphlet = MemorialPagePamphlet::factory()->create(['status' => PamphletStatus::PendingPayment]);
    $code = DiscountCode::factory()->create(['percent' => 20]);

    $this->actingAs($pamphlet->owner())->get(route('payments.checkout', $pamphlet));
    $this->actingAs($pamphlet->owner())
        ->post(route('payments.discount.apply', $pamphlet), ['code' => $code->code]);

    expect($code->fresh()->reserved_transaction_id)->not->toBeNull();

    $this->actingAs($pamphlet->owner())
        ->get(route('payments.cancel', $pamphlet))
        ->assertRedirect();

    expect($code->fresh()->reserved_transaction_id)->toBeNull()
        ->and($code->fresh()->used_at)->toBeNull();
});

it('discounts the first living legacy payment only and keeps recurring full price', function () {
    Http::fake([
        'sandbox.payfast.co.za/eng/query/validate' => Http::response('VALID'),
    ]);

    $package = SubscriptionPackage::factory()->create([
        'slug' => 'living-legacy',
        'billing_interval' => 'monthly',
        'price_cents' => 9900,
        'is_active' => true,
    ]);
    $subscription = Subscription::factory()->create([
        'subscription_package_id' => $package->id,
        'status' => SubscriptionStatus::Pending,
    ]);
    $code = DiscountCode::factory()->forPackage($package)->create(['percent' => 50]);

    $this->actingAs($subscription->user)->get(route('subscriptions.checkout', $subscription));
    $this->actingAs($subscription->user)
        ->post(route('subscriptions.discount.apply', $subscription), ['code' => $code->formattedCode()])
        ->assertRedirect(route('subscriptions.checkout', $subscription));

    $transaction = $subscription->transactions()->latest()->first();

    expect($transaction->amount_cents)->toBe(4950);

    $this->actingAs($subscription->user)
        ->get(route('subscriptions.checkout', $subscription))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('amount_cents', 4950)
            ->where('recurring_amount_cents', 9900)
            ->where('payload.amount', '49.50')
            ->where('payload.recurring_amount', '99.00'));

    $payload = signDiscountItnPayload([
        'm_payment_id' => $transaction->merchant_reference,
        'pf_payment_id' => '2089250',
        'payment_status' => 'COMPLETE',
        'item_name' => 'Living Legacy subscription',
        'amount_gross' => '49.50',
        'merchant_id' => config('services.payfast.merchant_id'),
        'token' => 'pf-subscription-token-123',
    ]);

    $this->post(route('subscriptions.notify', $subscription), $payload)->assertOk();

    expect($code->fresh()->used_at)->not->toBeNull()
        ->and($subscription->fresh()->status)->toBe(SubscriptionStatus::Active);

    $renewalPayload = signDiscountItnPayload([
        'm_payment_id' => $transaction->merchant_reference,
        'pf_payment_id' => '2089251',
        'payment_status' => 'COMPLETE',
        'item_name' => 'Living Legacy subscription',
        'amount_gross' => '99.00',
        'merchant_id' => config('services.payfast.merchant_id'),
        'token' => 'pf-subscription-token-123',
    ]);

    $this->post(route('subscriptions.notify', $subscription), $renewalPayload)->assertOk();

    $renewal = $subscription->transactions()->whereKeyNot($transaction->id)->first();

    expect($subscription->transactions()->count())->toBe(2)
        ->and($renewal->amount_cents)->toBe(9900)
        ->and($renewal->discount_code_id)->toBeNull()
        ->and($code->fresh()->used_transaction_id)->toBe($transaction->id);
});

it('rejects a 100 percent living legacy discount', function () {
    $package = SubscriptionPackage::factory()->create([
        'billing_interval' => 'monthly',
        'price_cents' => 9900,
        'is_active' => true,
    ]);
    $subscription = Subscription::factory()->create([
        'subscription_package_id' => $package->id,
        'status' => SubscriptionStatus::Pending,
    ]);
    $code = DiscountCode::factory()->create(['percent' => 100]);

    $this->actingAs($subscription->user)->get(route('subscriptions.checkout', $subscription));
    $this->actingAs($subscription->user)
        ->post(route('subscriptions.discount.apply', $subscription), ['code' => $code->formattedCode()])
        ->assertSessionHasErrors('code');
});

it('discounts provider credit PayFast checkout', function () {
    Http::fake([
        'sandbox.payfast.co.za/eng/query/validate' => Http::response('VALID'),
    ]);

    $serviceProvider = ServiceProvider::factory()->active()->create(['credits_remaining' => 0]);
    $owner = User::factory()->create();
    ServiceProviderUser::factory()->owner()->create([
        'service_provider_id' => $serviceProvider->id,
        'user_id' => $owner->id,
    ]);

    $package = ServiceProviderCreditPackage::factory()->create([
        'page_count' => 5,
        'price_cents' => 100000,
    ]);
    $purchase = ServiceProviderCreditPurchase::factory()->create([
        'service_provider_id' => $serviceProvider->id,
        'service_provider_credit_package_id' => $package->id,
        'purchased_by_user_id' => $owner->id,
        'package_name' => $package->name,
        'page_count' => 5,
        'price_cents' => 100000,
        'payment_method' => ServiceProviderCreditPaymentMethod::Payfast,
        'status' => ServiceProviderCreditPurchaseStatus::PendingPayment,
    ]);
    $code = DiscountCode::factory()->create([
        'discount_type' => DiscountType::Fixed,
        'percent' => null,
        'amount_cents' => 25000,
        'applies_to_all' => true,
    ]);

    $this->actingAs($owner)->get(route('provider.credits.checkout', $purchase))->assertOk();
    $this->actingAs($owner)
        ->post(route('provider.credits.discount.apply', $purchase), ['code' => $code->formattedCode()])
        ->assertRedirect(route('provider.credits.checkout', $purchase));

    $transaction = $purchase->transactions()->latest()->first();

    expect($transaction->amount_cents)->toBe(75000)
        ->and($purchase->fresh()->price_cents)->toBe(75000);

    $payload = signDiscountItnPayload([
        'm_payment_id' => $transaction->merchant_reference,
        'pf_payment_id' => '999001',
        'payment_status' => 'COMPLETE',
        'item_name' => $package->name,
        'amount_gross' => '750.00',
        'amount_fee' => '-10.00',
        'amount_net' => '740.00',
        'merchant_id' => config('services.payfast.merchant_id'),
    ]);

    $this->post(route('provider.credits.notify', $purchase), $payload)->assertOk();

    expect($code->fresh()->used_at)->not->toBeNull()
        ->and($purchase->fresh()->status)->toBe(ServiceProviderCreditPurchaseStatus::Released)
        ->and($serviceProvider->fresh()->credits_remaining)->toBe(5);
});

it('applies legacy memorial-page codes to funeral-memorial checkout', function () {
    SubscriptionPackage::factory()->create([
        'slug' => 'funeral-memorial',
        'billing_interval' => 'once_off',
        'price_cents' => 69900,
        'is_active' => true,
    ]);

    $legacyPackage = SubscriptionPackage::factory()->create([
        'slug' => 'memorial-page',
        'billing_interval' => 'once_off',
        'price_cents' => 69900,
        'is_active' => false,
    ]);
    $pamphlet = MemorialPagePamphlet::factory()->create(['status' => PamphletStatus::PendingPayment]);
    $code = DiscountCode::factory()->forPackage($legacyPackage)->create(['percent' => 10]);

    $this->actingAs($pamphlet->owner())->get(route('payments.checkout', $pamphlet))->assertOk();

    $this->actingAs($pamphlet->owner())
        ->post(route('payments.discount.apply', $pamphlet), ['code' => $code->formattedCode()])
        ->assertRedirect(route('payments.checkout', $pamphlet));
});

it('fulfills a zero amount pamphlet purchase without PayFast', function () {
    SubscriptionPackage::factory()->create([
        'slug' => 'funeral-memorial',
        'billing_interval' => 'once_off',
        'price_cents' => 69900,
        'is_active' => true,
    ]);

    $pamphlet = MemorialPagePamphlet::factory()->create(['status' => PamphletStatus::PendingPayment]);
    $code = DiscountCode::factory()->create(['percent' => 100]);

    $this->actingAs($pamphlet->owner())->get(route('payments.checkout', $pamphlet));
    $this->actingAs($pamphlet->owner())
        ->post(route('payments.discount.apply', $pamphlet), ['code' => $code->formattedCode()])
        ->assertRedirect(route('memorial.edit', $pamphlet));

    expect($pamphlet->fresh()->status)->toBe(PamphletStatus::Paid)
        ->and($code->fresh()->used_at)->not->toBeNull()
        ->and(Transaction::query()->first()?->status)->toBe(TransactionStatus::Complete)
        ->and(Transaction::query()->first()?->type)->toBe(TransactionType::PamphletPurchase);
});
