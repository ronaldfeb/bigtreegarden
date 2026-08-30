<?php

namespace App\Http\Controllers\Provider;

use App\Enums\ServiceProviderCreditPaymentMethod;
use App\Enums\ServiceProviderCreditPurchaseStatus;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Http\Requests\Provider\StoreCreditPurchaseRequest;
use App\Http\Requests\Provider\UploadProofOfPaymentRequest;
use App\Models\PlatformBankDetail;
use App\Models\ServiceProviderCreditPackage;
use App\Models\ServiceProviderCreditPurchase;
use App\Models\Transaction;
use App\Services\DiscountCodeService;
use App\Services\PayfastService;
use App\Services\ServiceProviderCreditService;
use App\Support\MediaStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class CreditController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $serviceProvider = $this->currentProvider($request);

        $packages = ServiceProviderCreditPackage::query()
            ->active()
            ->orderBy('sort_order')
            ->get()
            ->map(fn (ServiceProviderCreditPackage $package): array => [
                'id' => $package->id,
                'name' => $package->name,
                'description' => $package->description,
                'page_count' => $package->page_count,
                'price_cents' => $package->price_cents,
                'currency' => $package->currency,
            ]);

        $purchases = ServiceProviderCreditPurchase::query()
            ->where('service_provider_id', $serviceProvider->id)
            ->latest()
            ->paginate(10)
            ->through(fn (ServiceProviderCreditPurchase $purchase): array => [
                'id' => $purchase->id,
                'package_name' => $purchase->package_name,
                'page_count' => $purchase->page_count,
                'price_cents' => $purchase->price_cents,
                'currency' => $purchase->currency,
                'payment_method' => $purchase->payment_method->value,
                'status' => $purchase->status->value,
                'payment_reference' => $purchase->payment_reference,
                'created_at' => $purchase->created_at?->toIso8601String(),
                'released_at' => $purchase->released_at?->toIso8601String(),
            ]);

        return Inertia::render('provider/Credits/Index', [
            'creditsRemaining' => $serviceProvider->credits_remaining,
            'packages' => $packages,
            'purchases' => $purchases,
            'bankDetails' => PlatformBankDetail::current()?->only([
                'bank_name',
                'account_name',
                'account_number',
                'branch_code',
                'reference_note',
            ]),
        ]);
    }

    public function store(StoreCreditPurchaseRequest $request): RedirectResponse
    {
        $serviceProvider = $this->currentProvider($request);
        $package = ServiceProviderCreditPackage::query()
            ->active()
            ->whereKey($request->validated('service_provider_credit_package_id'))
            ->firstOrFail();

        $paymentMethod = ServiceProviderCreditPaymentMethod::from($request->validated('payment_method'));

        $purchase = ServiceProviderCreditPurchase::query()->create([
            'service_provider_id' => $serviceProvider->id,
            'service_provider_credit_package_id' => $package->id,
            'purchased_by_user_id' => $request->user()->id,
            'package_name' => $package->name,
            'page_count' => $package->page_count,
            'price_cents' => $package->price_cents,
            'currency' => $package->currency,
            'payment_method' => $paymentMethod,
            'status' => ServiceProviderCreditPurchaseStatus::PendingPayment,
            'payment_reference' => 'SP-'.Str::upper(Str::random(12)),
        ]);

        if ($paymentMethod === ServiceProviderCreditPaymentMethod::Payfast) {
            return redirect()->route('provider.credits.checkout', $purchase);
        }

        return redirect()->route('provider.credits.bank-transfer', $purchase);
    }

    public function checkout(
        Request $request,
        ServiceProviderCreditPurchase $purchase,
        PayfastService $payfastService,
        DiscountCodeService $discountCodeService,
    ): InertiaResponse|RedirectResponse {
        $this->assertOwnsPurchase($request, $purchase);

        if ($purchase->status === ServiceProviderCreditPurchaseStatus::Released) {
            return redirect()->route('provider.credits.index')
                ->with('status', 'This purchase has already been released.');
        }

        abort_unless(
            $purchase->payment_method === ServiceProviderCreditPaymentMethod::Payfast,
            422,
            'This purchase is not a PayFast payment.',
        );

        $transaction = $this->pendingTransaction($request, $purchase);

        return Inertia::render('provider/Credits/Checkout', [
            'checkoutUrl' => $payfastService->checkoutUrl(),
            'payload' => $payfastService->buildProviderCreditCheckoutPayload($purchase, $transaction),
            'purchase' => [
                'id' => $purchase->id,
                'package_name' => $purchase->package_name,
                'page_count' => $purchase->page_count,
                'price_cents' => $purchase->price_cents,
                'currency' => $purchase->currency,
                'payment_reference' => $purchase->payment_reference,
            ],
            'amount_cents' => $transaction->amount_cents,
            'original_amount_cents' => $transaction->original_amount_cents ?? $transaction->amount_cents,
            'autoSubmit' => false,
            'discount' => $discountCodeService->checkoutPayload($transaction),
        ]);
    }

    public function applyDiscount(
        Request $request,
        ServiceProviderCreditPurchase $purchase,
        DiscountCodeService $discountCodeService,
        ServiceProviderCreditService $creditService,
    ): RedirectResponse {
        $this->assertOwnsPurchase($request, $purchase);
        abort_unless(
            $purchase->status === ServiceProviderCreditPurchaseStatus::PendingPayment,
            422,
            'This purchase can no longer accept a discount code.',
        );

        $request->validate([
            'code' => ['required', 'string', 'max:32'],
        ]);

        $package = $purchase->package;
        abort_if($package === null, 422, 'Credit package is missing.');

        $transaction = $this->pendingTransaction($request, $purchase);
        $discountCodeService->apply($transaction, $request->string('code')->toString(), $package);
        $transaction->refresh();

        $purchase->update([
            'price_cents' => $transaction->amount_cents,
        ]);

        if (
            $purchase->payment_method === ServiceProviderCreditPaymentMethod::Payfast
            && $transaction->amount_cents <= 0
        ) {
            $transaction->update([
                'status' => TransactionStatus::Complete,
                'paid_at' => now(),
                'raw_payload' => ['source' => 'discount_code_zero'],
            ]);
            $discountCodeService->consume($transaction->fresh(), $request->user());
            $creditService->releasePurchase($purchase->fresh(), $request->user());

            return redirect()->route('provider.credits.index')
                ->with('status', 'Discount applied. Your credits are available — no payment required.');
        }

        return redirect()->route(
            $purchase->payment_method === ServiceProviderCreditPaymentMethod::Payfast
                ? 'provider.credits.checkout'
                : 'provider.credits.bank-transfer',
            $purchase,
        );
    }

    public function removeDiscount(
        Request $request,
        ServiceProviderCreditPurchase $purchase,
        DiscountCodeService $discountCodeService,
    ): RedirectResponse {
        $this->assertOwnsPurchase($request, $purchase);

        $transaction = $purchase->transactions()
            ->where('status', TransactionStatus::Initiated)
            ->latest()
            ->first();

        if ($transaction !== null) {
            $discountCodeService->release($transaction);
            $transaction->refresh();
            $purchase->update([
                'price_cents' => $transaction->amount_cents,
            ]);
        }

        return redirect()->route(
            $purchase->payment_method === ServiceProviderCreditPaymentMethod::Payfast
                ? 'provider.credits.checkout'
                : 'provider.credits.bank-transfer',
            $purchase,
        );
    }

    public function bankTransfer(
        Request $request,
        ServiceProviderCreditPurchase $purchase,
        DiscountCodeService $discountCodeService,
    ): InertiaResponse {
        $this->assertOwnsPurchase($request, $purchase);

        abort_unless(
            $purchase->payment_method === ServiceProviderCreditPaymentMethod::BankTransfer,
            422,
        );

        $transaction = $this->pendingTransaction($request, $purchase);

        return Inertia::render('provider/Credits/BankTransfer', [
            'purchase' => [
                'id' => $purchase->id,
                'package_name' => $purchase->package_name,
                'page_count' => $purchase->page_count,
                'price_cents' => $purchase->price_cents,
                'currency' => $purchase->currency,
                'payment_reference' => $purchase->payment_reference,
                'status' => $purchase->status->value,
                'proof_of_payment_path' => $purchase->proof_of_payment_path,
            ],
            'amount_cents' => $transaction->amount_cents,
            'original_amount_cents' => $transaction->original_amount_cents ?? $transaction->amount_cents,
            'discount' => $discountCodeService->checkoutPayload($transaction),
            'bankDetails' => PlatformBankDetail::current()?->only([
                'bank_name',
                'account_name',
                'account_number',
                'branch_code',
                'reference_note',
            ]),
        ]);
    }

    public function uploadProof(
        UploadProofOfPaymentRequest $request,
        ServiceProviderCreditPurchase $purchase,
    ): RedirectResponse {
        $this->assertOwnsPurchase($request, $purchase);

        abort_unless(
            $purchase->payment_method === ServiceProviderCreditPaymentMethod::BankTransfer,
            422,
        );

        abort_unless(
            in_array($purchase->status, [
                ServiceProviderCreditPurchaseStatus::PendingPayment,
                ServiceProviderCreditPurchaseStatus::PendingReview,
            ], true),
            422,
            'This purchase can no longer accept a proof of payment.',
        );

        $path = $request->file('proof_of_payment')->store(
            "providers/{$purchase->service_provider_id}/pop",
            MediaStorage::disk(),
        );

        $purchase->update([
            'proof_of_payment_path' => $path,
            'status' => ServiceProviderCreditPurchaseStatus::PendingReview,
        ]);

        return redirect()->route('provider.credits.index')
            ->with('status', 'Proof of payment uploaded. Our team will review it shortly.');
    }

    public function handleReturn(Request $request, ServiceProviderCreditPurchase $purchase): RedirectResponse
    {
        $this->assertOwnsPurchase($request, $purchase);

        $purchase->refresh();

        $message = $purchase->status === ServiceProviderCreditPurchaseStatus::Released
            ? 'Payment received. Your memorial page credits are available.'
            : 'Your payment is being processed. This may take a moment.';

        return redirect()->route('provider.credits.index')->with('status', $message);
    }

    public function handleCancel(
        Request $request,
        ServiceProviderCreditPurchase $purchase,
        DiscountCodeService $discountCodeService,
    ): RedirectResponse {
        $this->assertOwnsPurchase($request, $purchase);

        $transaction = $purchase->transactions()->latest()->first();

        if ($transaction !== null) {
            $discountCodeService->release($transaction);
            $transaction->update([
                'status' => TransactionStatus::Cancelled,
            ]);
            $transaction->refresh();
            $purchase->update([
                'price_cents' => $transaction->amount_cents,
            ]);
        }

        if ($purchase->status === ServiceProviderCreditPurchaseStatus::PendingPayment) {
            $purchase->update(['status' => ServiceProviderCreditPurchaseStatus::Cancelled]);
        }

        return redirect()->route('provider.credits.index')
            ->with('status', 'Payment was cancelled.');
    }

    public function handleNotify(
        Request $request,
        ServiceProviderCreditPurchase $purchase,
        PayfastService $payfastService,
        ServiceProviderCreditService $creditService,
        DiscountCodeService $discountCodeService,
    ): Response {
        $payload = $request->all();
        $paramString = $payfastService->buildItnParameterString($payload);

        $transaction = Transaction::query()
            ->where('payable_type', ServiceProviderCreditPurchase::class)
            ->where('payable_id', $purchase->id)
            ->where('merchant_reference', $request->string('m_payment_id')->toString())
            ->first();

        if ($transaction === null) {
            Log::warning('PayFast ITN received for unknown provider credit transaction.', [
                'purchase_id' => $purchase->id,
                'm_payment_id' => $request->string('m_payment_id')->toString(),
            ]);

            return response('OK', 200);
        }

        $signatureValid = $payfastService->isValidItnSignature($payload, $paramString);
        $amountValid = $payfastService->isValidItnAmount($transaction, $payload);
        $serverConfirmed = $payfastService->confirmItnWithPayfast($paramString);

        if (! $signatureValid || ! $amountValid || ! $serverConfirmed) {
            Log::warning('PayFast provider credit ITN failed security checks.', [
                'purchase_id' => $purchase->id,
                'transaction_id' => $transaction->id,
            ]);

            return response('OK', 200);
        }

        $status = $request->string('payment_status')->upper()->toString();

        if ($status === 'COMPLETE') {
            $transaction->update([
                'provider_payment_id' => $request->string('pf_payment_id')->toString() ?: null,
                'status' => TransactionStatus::Complete,
                'paid_at' => now(),
                'raw_payload' => $payload,
            ]);

            $discountCodeService->consume($transaction->fresh(), $purchase->purchasedBy);
            $creditService->releasePurchase($purchase->fresh());
        } else {
            $discountCodeService->release($transaction);

            $transaction->update([
                'provider_payment_id' => $request->string('pf_payment_id')->toString() ?: null,
                'status' => TransactionStatus::Failed,
                'raw_payload' => $payload,
            ]);
        }

        return response('OK', 200);
    }

    private function pendingTransaction(Request $request, ServiceProviderCreditPurchase $purchase): Transaction
    {
        $transaction = $purchase->transactions()
            ->where('status', TransactionStatus::Initiated)
            ->latest()
            ->first();

        $listPrice = $purchase->package?->price_cents ?? $purchase->price_cents;

        if ($transaction === null) {
            return $purchase->transactions()->create([
                'user_id' => $request->user()->id,
                'type' => TransactionType::ProviderCreditPurchase,
                'provider' => $purchase->payment_method === ServiceProviderCreditPaymentMethod::Payfast
                    ? 'payfast'
                    : 'bank_transfer',
                'merchant_reference' => (string) Str::ulid(),
                'amount_cents' => $listPrice,
                'currency' => $purchase->currency,
                'status' => TransactionStatus::Initiated,
            ]);
        }

        if ($transaction->discount_code_id === null && $transaction->amount_cents !== $listPrice) {
            $transaction->update(['amount_cents' => $listPrice]);
            $purchase->update(['price_cents' => $listPrice]);
        }

        return $transaction->fresh();
    }

    private function assertOwnsPurchase(Request $request, ServiceProviderCreditPurchase $purchase): void
    {
        $serviceProvider = $this->currentProvider($request);

        abort_unless($purchase->service_provider_id === $serviceProvider->id, 404);
    }
}
