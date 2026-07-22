<?php

use App\Enums\PamphletStatus;
use App\Enums\TransactionStatus;
use App\Models\MemorialPagePamphlet;
use App\Models\SubscriptionPackage;
use App\Models\Transaction;
use App\Services\PayfastService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    config()->set('services.payfast.merchant_id', '10000100');
    config()->set('services.payfast.merchant_key', '46f0cd694581a');
    config()->set('services.payfast.passphrase', 'test-passphrase');
    config()->set('services.payfast.url', 'https://sandbox.payfast.co.za/eng/process');

    SubscriptionPackage::factory()->create([
        'slug' => 'memorial-page',
        'billing_interval' => 'once_off',
        'price_cents' => 69900,
        'currency' => 'ZAR',
        'is_active' => true,
    ]);
});

/**
 * @param  array<string, mixed>  $data
 * @return array<string, mixed>
 */
function signItnPayload(array $data): array
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
function baseItnPayload(Transaction $transaction, array $overrides = []): array
{
    return array_merge([
        'm_payment_id' => $transaction->merchant_reference,
        'pf_payment_id' => '1089250',
        'payment_status' => 'COMPLETE',
        'item_name' => 'Memorial pamphlet',
        'amount_gross' => number_format($transaction->amount_cents / 100, 2, '.', ''),
        'amount_fee' => '-4.60',
        'amount_net' => number_format(($transaction->amount_cents / 100) - 4.60, 2, '.', ''),
        'merchant_id' => config('services.payfast.merchant_id'),
    ], $overrides);
}

it('includes a valid signature in the checkout payload', function () {
    $pamphlet = MemorialPagePamphlet::factory()->create([
        'status' => PamphletStatus::PendingPayment,
    ]);

    $transaction = Transaction::factory()->create([
        'payable_type' => MemorialPagePamphlet::class,
        'payable_id' => $pamphlet->id,
        'user_id' => $pamphlet->owner()?->id,
        'status' => TransactionStatus::Initiated,
        'amount_cents' => 69900,
    ]);

    $payfastService = app(PayfastService::class);
    $payload = $payfastService->buildCheckoutPayload($pamphlet, $transaction);

    expect($payload)->toHaveKey('signature');
    expect($payload['signature'])->toBeString()->not->toBeEmpty();
    expect($payload['m_payment_id'])->toBe($transaction->merchant_reference);

    $signatureFields = $payload;
    unset($signatureFields['signature']);

    $expectedSignature = $payfastService->generateSignature(
        $signatureFields,
        config('services.payfast.passphrase'),
    );

    expect($payload['signature'])->toBe($expectedSignature);
});

it('reuses an initiated transaction on repeated checkout visits', function () {
    $pamphlet = MemorialPagePamphlet::factory()->create([
        'status' => PamphletStatus::PendingPayment,
    ]);

    $this->actingAs($pamphlet->owner())->get(route('payments.checkout', $pamphlet));
    $this->actingAs($pamphlet->owner())->get(route('payments.checkout', $pamphlet));

    expect(Transaction::query()->count())->toBe(1);
});

it('prices pamphlet checkout from the memorial-page subscription package', function () {
    SubscriptionPackage::query()->where('slug', 'memorial-page')->update([
        'price_cents' => 45500,
        'currency' => 'ZAR',
    ]);

    $pamphlet = MemorialPagePamphlet::factory()->create([
        'status' => PamphletStatus::PendingPayment,
    ]);

    $this->actingAs($pamphlet->owner())->get(route('payments.checkout', $pamphlet))->assertSuccessful();

    $transaction = Transaction::query()->first();

    expect($transaction)->not->toBeNull();
    expect($transaction->amount_cents)->toBe(45500);
    expect($transaction->currency)->toBe('ZAR');
});

it('updates an initiated transaction when package pricing changes', function () {
    $pamphlet = MemorialPagePamphlet::factory()->create([
        'status' => PamphletStatus::PendingPayment,
    ]);

    Transaction::factory()->create([
        'payable_type' => MemorialPagePamphlet::class,
        'payable_id' => $pamphlet->id,
        'user_id' => $pamphlet->owner()?->id,
        'status' => TransactionStatus::Initiated,
        'amount_cents' => 10000,
        'currency' => 'ZAR',
    ]);

    SubscriptionPackage::query()->where('slug', 'memorial-page')->update([
        'price_cents' => 77700,
    ]);

    $this->actingAs($pamphlet->owner())->get(route('payments.checkout', $pamphlet))->assertSuccessful();

    expect(Transaction::query()->count())->toBe(1);
    expect(Transaction::query()->first()?->amount_cents)->toBe(77700);
});

it('fulfills transaction and pamphlet when a valid ITN is received', function () {
    Http::fake([
        'sandbox.payfast.co.za/eng/query/validate' => Http::response('VALID'),
    ]);

    $pamphlet = MemorialPagePamphlet::factory()->create([
        'status' => PamphletStatus::PendingPayment,
        'paid_at' => null,
    ]);

    $transaction = Transaction::factory()->create([
        'payable_type' => MemorialPagePamphlet::class,
        'payable_id' => $pamphlet->id,
        'user_id' => $pamphlet->owner()?->id,
        'status' => TransactionStatus::Initiated,
        'amount_cents' => 69900,
    ]);

    $payload = signItnPayload(baseItnPayload($transaction));

    $response = $this->post(route('payments.notify', $pamphlet), $payload);

    $response->assertOk();

    $pamphlet->refresh();
    $transaction->refresh();

    expect($transaction->status)->toBe(TransactionStatus::Complete);
    expect($transaction->provider_payment_id)->toBe('1089250');
    expect($pamphlet->status)->toBe(PamphletStatus::Paid);
    expect($pamphlet->memorialPage?->personOfInterest?->qr_code_path)->not->toBeNull();
});

it('does not fulfill when the ITN signature is invalid', function () {
    Http::fake([
        'sandbox.payfast.co.za/eng/query/validate' => Http::response('VALID'),
    ]);

    $pamphlet = MemorialPagePamphlet::factory()->create([
        'status' => PamphletStatus::PendingPayment,
    ]);

    $transaction = Transaction::factory()->create([
        'payable_type' => MemorialPagePamphlet::class,
        'payable_id' => $pamphlet->id,
        'user_id' => $pamphlet->owner()?->id,
        'status' => TransactionStatus::Initiated,
        'amount_cents' => 69900,
    ]);

    $payload = baseItnPayload($transaction);
    $payload['signature'] = 'invalid-signature';

    $response = $this->post(route('payments.notify', $pamphlet), $payload);

    $response->assertOk();

    $pamphlet->refresh();
    $transaction->refresh();

    expect($transaction->status)->toBe(TransactionStatus::Initiated);
    expect($pamphlet->status)->toBe(PamphletStatus::PendingPayment);
    expect($pamphlet->memorialPage?->personOfInterest?->qr_code_path)->toBeNull();
});

it('does not fulfill when the ITN amount does not match', function () {
    Http::fake([
        'sandbox.payfast.co.za/eng/query/validate' => Http::response('VALID'),
    ]);

    $pamphlet = MemorialPagePamphlet::factory()->create([
        'status' => PamphletStatus::PendingPayment,
    ]);

    $transaction = Transaction::factory()->create([
        'payable_type' => MemorialPagePamphlet::class,
        'payable_id' => $pamphlet->id,
        'user_id' => $pamphlet->owner()?->id,
        'status' => TransactionStatus::Initiated,
        'amount_cents' => 69900,
    ]);

    $payload = signItnPayload(baseItnPayload($transaction, [
        'amount_gross' => '1.00',
    ]));

    $response = $this->post(route('payments.notify', $pamphlet), $payload);

    $response->assertOk();

    $pamphlet->refresh();
    $transaction->refresh();

    expect($transaction->status)->toBe(TransactionStatus::Initiated);
    expect($pamphlet->status)->toBe(PamphletStatus::PendingPayment);
});

it('does not mark a pamphlet as paid from the return URL alone', function () {
    $pamphlet = MemorialPagePamphlet::factory()->create([
        'status' => PamphletStatus::PendingPayment,
        'paid_at' => null,
    ]);

    Transaction::factory()->create([
        'payable_type' => MemorialPagePamphlet::class,
        'payable_id' => $pamphlet->id,
        'user_id' => $pamphlet->owner()?->id,
        'status' => TransactionStatus::Initiated,
    ]);

    $response = $this->actingAs($pamphlet->owner())->get(route('payments.return', $pamphlet));

    $response->assertRedirect(route('memorial.edit', $pamphlet));

    $pamphlet->refresh();

    expect($pamphlet->status)->toBe(PamphletStatus::PendingPayment);
    expect($pamphlet->memorialPage?->personOfInterest?->qr_code_path)->toBeNull();
});

it('redirects paid pamphlets away from checkout', function () {
    $pamphlet = MemorialPagePamphlet::factory()->create([
        'status' => PamphletStatus::Paid,
    ]);

    $response = $this->actingAs($pamphlet->owner())->get(route('payments.checkout', $pamphlet));

    $response->assertRedirect(route('memorial.edit', $pamphlet));
});
