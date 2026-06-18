<?php

namespace App\Http\Controllers;

use App\Models\Pamphlet;
use App\Models\Payment;
use App\Services\GuestPamphletDraftService;
use App\Services\PamphletPaymentFulfillmentService;
use App\Services\PayfastService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class PaymentController extends Controller
{
    public function continueToCheckout(
        Pamphlet $pamphlet,
        GuestPamphletDraftService $guestPamphletDraftService
    ): RedirectResponse {
        abort_unless($guestPamphletDraftService->requestOwnsPamphlet(request(), $pamphlet), 403);

        if (request()->user() === null) {
            session(['url.intended' => route('pamphlets.continue', $pamphlet)]);

            return redirect()->route('login');
        }

        if ($pamphlet->user_id === null) {
            $guestPamphletDraftService->claimPamphletToUser($pamphlet, request()->user());
        }

        $pamphlet->update(['status' => 'pending_payment']);

        return redirect()
            ->route('payments.checkout', $pamphlet)
            ->withCookie($guestPamphletDraftService->clearCookie());
    }

    public function checkout(Pamphlet $pamphlet, PayfastService $payfastService): InertiaResponse|RedirectResponse
    {
        abort_if($pamphlet->user_id !== request()->user()->id, 403);

        if (in_array($pamphlet->status, ['paid', 'published'], true)) {
            return redirect()->route('memorial.edit', $pamphlet);
        }

        $payment = $pamphlet->payments()
            ->where('status', 'initiated')
            ->latest()
            ->first();

        if ($payment === null) {
            $payment = $pamphlet->payments()->create([
                'provider' => 'payfast',
                'amount_cents' => config('memorial.fixed_price_cents'),
                'currency' => 'ZAR',
                'status' => 'initiated',
            ]);
        }

        return Inertia::render('payments/Checkout', [
            'checkoutUrl' => $payfastService->checkoutUrl(),
            'payload' => $payfastService->buildCheckoutPayload($pamphlet, $payment),
            'paymentId' => $payment->id,
        ]);
    }

    public function handleReturn(Pamphlet $pamphlet): RedirectResponse
    {
        abort_if($pamphlet->user_id !== request()->user()->id, 403);

        $pamphlet->refresh();

        $message = in_array($pamphlet->status, ['paid', 'published'], true)
            ? 'Payment received. You can now complete your memorial page.'
            : 'Your payment is being processed. This may take a moment.';

        return redirect()
            ->route('memorial.edit', $pamphlet)
            ->with('status', $message);
    }

    public function handleCancel(Pamphlet $pamphlet): RedirectResponse
    {
        abort_if($pamphlet->user_id !== request()->user()->id, 403);

        $pamphlet->payments()->latest()->first()?->update([
            'status' => 'cancelled',
        ]);

        $pamphlet->update([
            'status' => 'draft',
        ]);

        return redirect()->route('pamphlets.show', $pamphlet);
    }

    public function handleNotify(
        Request $request,
        Pamphlet $pamphlet,
        PayfastService $payfastService,
        PamphletPaymentFulfillmentService $fulfillmentService
    ): Response {
        $payload = $request->all();
        $paramString = $payfastService->buildItnParameterString($payload);

        $payment = Payment::query()
            ->where('pamphlet_id', $pamphlet->id)
            ->whereKey($request->string('m_payment_id')->toString())
            ->first();

        if ($payment === null) {
            Log::warning('PayFast ITN received for unknown payment.', [
                'pamphlet_id' => $pamphlet->id,
                'm_payment_id' => $request->string('m_payment_id')->toString(),
            ]);

            return response('OK', 200);
        }

        $signatureValid = $payfastService->isValidItnSignature($payload, $paramString);
        $amountValid = $payfastService->isValidItnAmount($payment, $payload);
        $serverConfirmed = $payfastService->confirmItnWithPayfast($paramString);

        if (! $signatureValid || ! $amountValid || ! $serverConfirmed) {
            Log::warning('PayFast ITN failed security checks.', [
                'pamphlet_id' => $pamphlet->id,
                'payment_id' => $payment->id,
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
                $payment,
                $payload,
                $request->string('pf_payment_id')->toString() ?: null,
            );
        } else {
            $payment->update([
                'provider_payment_id' => $request->string('pf_payment_id')->toString() ?: null,
                'status' => 'failed',
                'raw_payload' => $payload,
            ]);
        }

        return response('OK', 200);
    }
}
