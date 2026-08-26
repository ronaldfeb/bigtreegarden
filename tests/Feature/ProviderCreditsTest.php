<?php

use App\Enums\ServiceProviderCreditPaymentMethod;
use App\Enums\ServiceProviderCreditPurchaseStatus;
use App\Enums\ServiceProviderRole;
use App\Enums\StaffRole;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\PlatformBankDetail;
use App\Models\ServiceProvider;
use App\Models\ServiceProviderCreditPackage;
use App\Models\ServiceProviderCreditPurchase;
use App\Models\ServiceProviderUser;
use App\Models\StaffUser;
use App\Models\Transaction;
use App\Models\User;
use App\Services\PayfastService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    config()->set('services.payfast.merchant_id', '10000100');
    config()->set('services.payfast.merchant_key', '46f0cd694581a');
    config()->set('services.payfast.passphrase', 'test-passphrase');
    config()->set('services.payfast.url', 'https://sandbox.payfast.co.za/eng/process');
});

function activeProviderOwner(): array
{
    $serviceProvider = ServiceProvider::factory()->active()->create(['credits_remaining' => 0]);
    $owner = User::factory()->create();
    ServiceProviderUser::factory()->owner()->create([
        'service_provider_id' => $serviceProvider->id,
        'user_id' => $owner->id,
    ]);

    return [$serviceProvider, $owner];
}

function directoryStaff(): User
{
    $user = User::factory()->create();
    StaffUser::factory()->create([
        'user_id' => $user->id,
        'role' => StaffRole::Support,
        'is_active' => true,
    ]);

    return $user->fresh(['staffUser']);
}

test('staff admins can create credit packages', function () {
    $admin = User::factory()->create();
    StaffUser::factory()->create([
        'user_id' => $admin->id,
        'role' => StaffRole::Admin,
        'is_active' => true,
    ]);

    $this->actingAs($admin)
        ->post(route('staff.commerce.credit-packages.store'), [
            'name' => 'Bulk 10',
            'description' => 'Ten pages',
            'page_count' => 10,
            'price_cents' => 500000,
            'currency' => 'ZAR',
            'is_active' => '1',
            'sort_order' => 1,
        ])
        ->assertRedirect();

    expect(ServiceProviderCreditPackage::query()->where('name', 'Bulk 10')->exists())->toBeTrue();
});

test('payfast itn releases credits to the provider', function () {
    Http::fake([
        'https://sandbox.payfast.co.za/eng/query/validate' => Http::response('VALID', 200),
    ]);

    [$serviceProvider, $owner] = activeProviderOwner();
    $package = ServiceProviderCreditPackage::factory()->create([
        'page_count' => 10,
        'price_cents' => 400000,
    ]);

    $purchase = ServiceProviderCreditPurchase::factory()->create([
        'service_provider_id' => $serviceProvider->id,
        'service_provider_credit_package_id' => $package->id,
        'purchased_by_user_id' => $owner->id,
        'package_name' => $package->name,
        'page_count' => 10,
        'price_cents' => 400000,
        'payment_method' => ServiceProviderCreditPaymentMethod::Payfast,
        'status' => ServiceProviderCreditPurchaseStatus::PendingPayment,
    ]);

    $transaction = Transaction::factory()->create([
        'user_id' => $owner->id,
        'payable_type' => ServiceProviderCreditPurchase::class,
        'payable_id' => $purchase->id,
        'type' => TransactionType::ProviderCreditPurchase,
        'status' => TransactionStatus::Initiated,
        'amount_cents' => 400000,
        'currency' => 'ZAR',
    ]);

    $payfastService = app(PayfastService::class);
    $payload = [
        'm_payment_id' => $transaction->merchant_reference,
        'pf_payment_id' => '999001',
        'payment_status' => 'COMPLETE',
        'item_name' => $package->name,
        'amount_gross' => '4000.00',
        'amount_fee' => '-10.00',
        'amount_net' => '3990.00',
        'merchant_id' => config('services.payfast.merchant_id'),
    ];
    $paramString = $payfastService->buildItnParameterString($payload);
    $payload['signature'] = md5($paramString.'&passphrase='.urlencode(config('services.payfast.passphrase')));

    $this->post(route('provider.credits.notify', $purchase), $payload)
        ->assertOk();

    expect($serviceProvider->fresh()->credits_remaining)->toBe(10)
        ->and($purchase->fresh()->status)->toBe(ServiceProviderCreditPurchaseStatus::Released);
});

test('bank transfer stays pending until staff release', function () {
    Storage::fake('public');
    PlatformBankDetail::factory()->create();
    [$serviceProvider, $owner] = activeProviderOwner();
    $package = ServiceProviderCreditPackage::factory()->create(['page_count' => 5, 'price_cents' => 200000]);

    $this->actingAs($owner)
        ->post(route('provider.credits.store'), [
            'service_provider_credit_package_id' => $package->id,
            'payment_method' => ServiceProviderCreditPaymentMethod::BankTransfer->value,
        ])
        ->assertRedirect();

    $purchase = ServiceProviderCreditPurchase::query()->latest()->first();

    $this->actingAs($owner)
        ->post(route('provider.credits.proof', $purchase), [
            'proof_of_payment' => UploadedFile::fake()->create('pop.pdf', 100, 'application/pdf'),
        ])
        ->assertRedirect(route('provider.credits.index'));

    expect($purchase->fresh()->status)->toBe(ServiceProviderCreditPurchaseStatus::PendingReview)
        ->and($serviceProvider->fresh()->credits_remaining)->toBe(0);

    $staff = directoryStaff();

    $this->actingAs($staff)
        ->post(route('staff.commerce.credit-purchases.release', $purchase))
        ->assertRedirect();

    expect($serviceProvider->fresh()->credits_remaining)->toBe(5)
        ->and($purchase->fresh()->status)->toBe(ServiceProviderCreditPurchaseStatus::Released);
});

test('rejecting a bank transfer does not credit the provider', function () {
    Storage::fake('public');
    [$serviceProvider, $owner] = activeProviderOwner();

    $purchase = ServiceProviderCreditPurchase::factory()->bankTransfer()->create([
        'service_provider_id' => $serviceProvider->id,
        'purchased_by_user_id' => $owner->id,
        'page_count' => 5,
        'status' => ServiceProviderCreditPurchaseStatus::PendingReview,
    ]);

    $staff = directoryStaff();

    $this->actingAs($staff)
        ->post(route('staff.commerce.credit-purchases.reject', $purchase), [
            'review_note' => 'Invalid reference',
        ])
        ->assertRedirect();

    expect($serviceProvider->fresh()->credits_remaining)->toBe(0)
        ->and($purchase->fresh()->status)->toBe(ServiceProviderCreditPurchaseStatus::Rejected);
});

test('staff members cannot purchase credits', function () {
    $serviceProvider = ServiceProvider::factory()->active()->create();
    $staff = User::factory()->create();
    ServiceProviderUser::factory()->create([
        'service_provider_id' => $serviceProvider->id,
        'user_id' => $staff->id,
        'role' => ServiceProviderRole::Staff,
    ]);

    $this->actingAs($staff)
        ->get(route('provider.credits.index'))
        ->assertForbidden();
});
