<?php

use App\Models\Pamphlet;
use App\Models\Payment;
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
function baseItnPayload(Payment $payment, array $overrides = []): array
{
    return array_merge([
        'm_payment_id' => $payment->id,
        'pf_payment_id' => '1089250',
        'payment_status' => 'COMPLETE',
        'item_name' => 'Memorial pamphlet',
        'amount_gross' => number_format($payment->amount_cents / 100, 2, '.', ''),
        'amount_fee' => '-4.60',
        'amount_net' => number_format(($payment->amount_cents / 100) - 4.60, 2, '.', ''),
        'merchant_id' => config('services.payfast.merchant_id'),
    ], $overrides);
}

it('includes a valid signature in the checkout payload', function () {
    $pamphlet = Pamphlet::factory()->create([
        'status' => 'pending_payment',
    ]);

    $payment = Payment::factory()->create([
        'pamphlet_id' => $pamphlet->id,
        'status' => 'initiated',
        'amount_cents' => config('memorial.fixed_price_cents'),
    ]);

    $payfastService = app(PayfastService::class);
    $payload = $payfastService->buildCheckoutPayload($pamphlet, $payment);

    expect($payload)->toHaveKey('signature');
    expect($payload['signature'])->toBeString()->not->toBeEmpty();
    expect($payload['m_payment_id'])->toBe($payment->id);

    $signatureFields = $payload;
    unset($signatureFields['signature']);

    $expectedSignature = $payfastService->generateSignature(
        $signatureFields,
        config('services.payfast.passphrase'),
    );

    expect($payload['signature'])->toBe($expectedSignature);
});

it('reuses an initiated payment on repeated checkout visits', function () {
    $pamphlet = Pamphlet::factory()->create([
        'status' => 'pending_payment',
    ]);

    $this->actingAs($pamphlet->user)->get(route('payments.checkout', $pamphlet));
    $this->actingAs($pamphlet->user)->get(route('payments.checkout', $pamphlet));

    expect(Payment::query()->count())->toBe(1);
});

it('fulfills payment and pamphlet when a valid ITN is received', function () {
    Http::fake([
        'sandbox.payfast.co.za/eng/query/validate' => Http::response('VALID'),
    ]);

    $pamphlet = Pamphlet::factory()->create([
        'status' => 'pending_payment',
        'paid_at' => null,
    ]);

    $payment = Payment::factory()->create([
        'pamphlet_id' => $pamphlet->id,
        'status' => 'initiated',
        'amount_cents' => config('memorial.fixed_price_cents'),
    ]);

    $payload = signItnPayload(baseItnPayload($payment));

    $response = $this->post(route('payments.notify', $pamphlet), $payload);

    $response->assertOk();

    $pamphlet->refresh();
    $payment->refresh();

    expect($payment->status)->toBe('paid');
    expect($payment->provider_payment_id)->toBe('1089250');
    expect($pamphlet->status)->toBe('paid');
    expect($pamphlet->pamphletQrCode)->not->toBeNull();
    expect($pamphlet->pamphletQrCode->target_url)->toContain('/memorial/');
});

it('does not fulfill when the ITN signature is invalid', function () {
    Http::fake([
        'sandbox.payfast.co.za/eng/query/validate' => Http::response('VALID'),
    ]);

    $pamphlet = Pamphlet::factory()->create([
        'status' => 'pending_payment',
    ]);

    $payment = Payment::factory()->create([
        'pamphlet_id' => $pamphlet->id,
        'status' => 'initiated',
        'amount_cents' => config('memorial.fixed_price_cents'),
    ]);

    $payload = baseItnPayload($payment);
    $payload['signature'] = 'invalid-signature';

    $response = $this->post(route('payments.notify', $pamphlet), $payload);

    $response->assertOk();

    $pamphlet->refresh();
    $payment->refresh();

    expect($payment->status)->toBe('initiated');
    expect($pamphlet->status)->toBe('pending_payment');
    expect($pamphlet->pamphletQrCode)->toBeNull();
});

it('does not fulfill when the ITN amount does not match', function () {
    Http::fake([
        'sandbox.payfast.co.za/eng/query/validate' => Http::response('VALID'),
    ]);

    $pamphlet = Pamphlet::factory()->create([
        'status' => 'pending_payment',
    ]);

    $payment = Payment::factory()->create([
        'pamphlet_id' => $pamphlet->id,
        'status' => 'initiated',
        'amount_cents' => config('memorial.fixed_price_cents'),
    ]);

    $payload = signItnPayload(baseItnPayload($payment, [
        'amount_gross' => '1.00',
    ]));

    $response = $this->post(route('payments.notify', $pamphlet), $payload);

    $response->assertOk();

    $pamphlet->refresh();
    $payment->refresh();

    expect($payment->status)->toBe('initiated');
    expect($pamphlet->status)->toBe('pending_payment');
});

it('does not mark a pamphlet as paid from the return URL alone', function () {
    $pamphlet = Pamphlet::factory()->create([
        'status' => 'pending_payment',
        'paid_at' => null,
    ]);

    Payment::factory()->create([
        'pamphlet_id' => $pamphlet->id,
        'status' => 'initiated',
    ]);

    $response = $this->actingAs($pamphlet->user)->get(route('payments.return', $pamphlet));

    $response->assertRedirect(route('memorial.edit', $pamphlet));

    $pamphlet->refresh();

    expect($pamphlet->status)->toBe('pending_payment');
    expect($pamphlet->pamphletQrCode)->toBeNull();
});

it('redirects paid pamphlets away from checkout', function () {
    $pamphlet = Pamphlet::factory()->create([
        'status' => 'paid',
    ]);

    $response = $this->actingAs($pamphlet->user)->get(route('payments.checkout', $pamphlet));

    $response->assertRedirect(route('memorial.edit', $pamphlet));
});
