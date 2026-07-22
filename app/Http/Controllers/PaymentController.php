<?php

namespace App\Http\Controllers;

use App\Enums\PamphletStatus;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\MemorialPagePamphlet;
use App\Models\SubscriptionPackage;
use App\Models\Transaction;
use App\Services\GuestPamphletDraftService;
use App\Services\PamphletPaymentFulfillmentService;
use App\Services\PayfastService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class PaymentController extends Controller
{
    public function continueToCheckout(
        MemorialPagePamphlet $pamphlet,
        GuestPamphletDraftService $guestPamphletDraftService
    ): RedirectResponse {
        abort_unless($guestPamphletDraftService->requestOwnsPamphlet(request(), $pamphlet), 403);

        if (request()->user() === null) {
            session(['url.intended' => route('pamphlets.continue', $pamphlet)]);

            return redirect()->route('login');
        }

        if ($pamphlet->owner() === null) {
            $guestPamphletDraftService->claimPamphletToUser($pamphlet, request()->user());
        }

        $pamphlet->update(['status' => PamphletStatus::PendingPayment]);

        return redirect()
            ->route('payments.checkout', $pamphlet)
            ->withCookie($guestPamphletDraftService->clearCookie());
    }

    public function checkout(MemorialPagePamphlet $pamphlet, PayfastService $payfastService): InertiaResponse|RedirectResponse
    {
        abort_unless($pamphlet->isOwnedBy(request()->user()), 403);

        if (in_array($pamphlet->status, [PamphletStatus::Paid, PamphletStatus::Published], true)) {
            return redirect()->route('memorial.edit', $pamphlet);
        }

        $package = SubscriptionPackage::memorialPagePackage();

        abort_if($package === null, 503, 'Memorial page pricing is not configured.');

        $transaction = $pamphlet->transactions()
            ->where('status', TransactionStatus::Initiated)
            ->latest()
            ->first();

        if ($transaction === null) {
            $transaction = $pamphlet->transactions()->create([
                'user_id' => request()->user()->id,
                'type' => TransactionType::PamphletPurchase,
                'provider' => 'payfast',
                'merchant_reference' => (string) Str::ulid(),
                'amount_cents' => $package->price_cents,
                'currency' => $package->currency,
                'status' => TransactionStatus::Initiated,
            ]);
        } elseif ($transaction->amount_cents !== $package->price_cents || $transaction->currency !== $package->currency) {
            $transaction->update([
                'amount_cents' => $package->price_cents,
                'currency' => $package->currency,
            ]);
        }

        return Inertia::render('payments/Checkout', [
            'checkoutUrl' => $payfastService->checkoutUrl(),
            'payload' => $payfastService->buildCheckoutPayload($pamphlet, $transaction),
            'paymentId' => $transaction->id,
        ]);
    }

    public function handleReturn(MemorialPagePamphlet $pamphlet): RedirectResponse
    {
        abort_unless($pamphlet->isOwnedBy(request()->user()), 403);

        $pamphlet->refresh();

        $message = in_array($pamphlet->status, [PamphletStatus::Paid, PamphletStatus::Published], true)
            ? 'Payment received. You can now complete your memorial page.'
            : 'Your payment is being processed. This may take a moment.';

        return redirect()
            ->route('memorial.edit', $pamphlet)
            ->with('status', $message);
    }

    public function handleCancel(MemorialPagePamphlet $pamphlet): RedirectResponse
    {
        abort_unless($pamphlet->isOwnedBy(request()->user()), 403);

        $pamphlet->transactions()->latest()->first()?->update([
            'status' => TransactionStatus::Cancelled,
        ]);

        $pamphlet->update([
            'status' => PamphletStatus::Draft,
        ]);

        return redirect()->route('pamphlets.show', $pamphlet);
    }

    public function handleNotify(
        Request $request,
        MemorialPagePamphlet $pamphlet,
        PayfastService $payfastService,
        PamphletPaymentFulfillmentService $fulfillmentService
    ): Response {
        $payload = $request->all();
        $paramString = $payfastService->buildItnParameterString($payload);

        $transaction = Transaction::query()
            ->where('payable_type', MemorialPagePamphlet::class)
            ->where('payable_id', $pamphlet->id)
            ->where('merchant_reference', $request->string('m_payment_id')->toString())
            ->first();

        if ($transaction === null) {
            Log::warning('PayFast ITN received for unknown transaction.', [
                'pamphlet_id' => $pamphlet->id,
                'm_payment_id' => $request->string('m_payment_id')->toString(),
            ]);

            return response('OK', 200);
        }

        $signatureValid = $payfastService->isValidItnSignature($payload, $paramString);
        $amountValid = $payfastService->isValidItnAmount($transaction, $payload);
        $serverConfirmed = $payfastService->confirmItnWithPayfast($paramString);

        if (! $signatureValid || ! $amountValid || ! $serverConfirmed) {
            Log::warning('PayFast ITN failed security checks.', [
                'pamphlet_id' => $pamphlet->id,
                'transaction_id' => $transaction->id,
                'signature_valid' => $signatureValid,
                'amount_valid' => $amountValid,
                'server_confirmed' => $serverConfirmed,
            ]);

            return response('OK', 200);
        }

        $status = $request->string('payment_status')->upper()->toString();

        if ($status === 'COMPLETE') {
            $fulfillmentService->fulfill(
                $pamphlet,
                $transaction,
                $payload,
                $request->string('pf_payment_id')->toString() ?: null,
            );
        } else {
            $transaction->update([
                'provider_payment_id' => $request->string('pf_payment_id')->toString() ?: null,
                'status' => TransactionStatus::Failed,
                'raw_payload' => $payload,
            ]);
        }

        return response('OK', 200);
    }
}
