<?php

namespace App\Http\Controllers;

use App\Models\Pamphlet;
use App\Models\Payment;
use App\Services\GuestPamphletDraftService;
use App\Services\PayfastService;
use App\Services\QrCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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

    public function checkout(Pamphlet $pamphlet, PayfastService $payfastService): InertiaResponse
    {
        abort_if($pamphlet->user_id !== request()->user()->id, 403);

        $payment = $pamphlet->payments()->create([
            'provider' => 'payfast',
            'amount_cents' => config('memorial.fixed_price_cents'),
            'currency' => 'ZAR',
            'status' => 'initiated',
        ]);

        return Inertia::render('payments/Checkout', [
            'checkoutUrl' => $payfastService->checkoutUrl(),
            'payload' => $payfastService->buildCheckoutPayload($pamphlet),
            'paymentId' => $payment->id,
        ]);
    }

    public function handleReturn(Pamphlet $pamphlet, QrCodeService $qrCodeService): RedirectResponse
    {
        $payment = $pamphlet->payments()->latest()->first();

        if ($payment !== null) {
            $payment->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);
        }

        $pamphlet->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $targetUrl = route('memorial.public.show', $pamphlet->public_slug);
        $pamphlet->pamphletQrCode()->updateOrCreate([], [
            'target_url' => $targetUrl,
            'image_path' => $qrCodeService->imageUrlForPamphlet($pamphlet),
            'generated_at' => now(),
        ]);

        return redirect()->route('memorial.edit', $pamphlet);
    }

    public function handleCancel(Pamphlet $pamphlet): RedirectResponse
    {
        $pamphlet->payments()->latest()->first()?->update([
            'status' => 'cancelled',
        ]);

        $pamphlet->update([
            'status' => 'draft',
        ]);

        return redirect()->route('pamphlets.show', $pamphlet);
    }

    public function handleNotify(Request $request, Pamphlet $pamphlet): Response
    {
        /** @var Payment|null $payment */
        $payment = $pamphlet->payments()->latest()->first();

        if ($payment !== null) {
            $status = $request->string('payment_status')->lower()->toString();

            $payment->update([
                'provider_payment_id' => $request->string('pf_payment_id')->toString() ?: null,
                'status' => $status === 'complete' ? 'paid' : 'failed',
                'paid_at' => $status === 'complete' ? now() : null,
                'raw_payload' => $request->all(),
            ]);
        }

        return response('OK', 200);
    }
}
