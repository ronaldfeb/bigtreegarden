<?php

namespace App\Http\Controllers;

use App\Enums\PamphletStatus;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\MemorialPagePamphlet;
use App\Models\SubscriptionPackage;
use App\Models\Transaction;
use App\Services\DiscountCodeService;
use App\Services\GuestPamphletDraftService;
use App\Services\PamphletPaymentFulfillmentService;
use App\Services\PayfastService;
use App\Support\MediaStorage;
use App\Support\PamphletLayout;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Laravel\Fortify\Features;

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

    public function checkout(
        MemorialPagePamphlet $pamphlet,
        PayfastService $payfastService,
        DiscountCodeService $discountCodeService,
    ): InertiaResponse|RedirectResponse {
        abort_unless($pamphlet->isOwnedBy(request()->user()), 403);

        if (in_array($pamphlet->status, [PamphletStatus::Paid, PamphletStatus::Published], true)) {
            return redirect()->route('memorial.edit', $pamphlet);
        }

        if ($pamphlet->status === PamphletStatus::Draft) {
            $pamphlet->update(['status' => PamphletStatus::PendingPayment]);
        }

        $checkout = $this->buildPayfastCheckout($pamphlet, $payfastService);

        $pamphlet->load(['background', 'style', 'memorialPage.personOfInterest']);

        return Inertia::render('payments/Checkout', [
            ...$checkout,
            'paymentId' => $checkout['payment_id'],
            'amount_cents' => $checkout['amount_cents'],
            'original_amount_cents' => $checkout['original_amount_cents'],
            'currency' => $checkout['currency'],
            'autoSubmit' => false,
            'discount' => $discountCodeService->checkoutPayload(
                Transaction::query()->find($checkout['payment_id']),
            ),
            'pamphlet' => $this->pamphletPreviewPayload($pamphlet),
            'pricing' => $this->memorialPricingPayload(),
            'canRegister' => Features::enabled(Features::registration()),
        ]);
    }

    public function applyDiscount(
        Request $request,
        MemorialPagePamphlet $pamphlet,
        DiscountCodeService $discountCodeService,
        PamphletPaymentFulfillmentService $fulfillmentService,
    ): RedirectResponse {
        abort_unless($pamphlet->isOwnedBy($request->user()), 403);
        abort_if(
            in_array($pamphlet->status, [PamphletStatus::Paid, PamphletStatus::Published], true),
            422,
            'This pamphlet has already been paid.',
        );

        $request->validate([
            'code' => ['required', 'string', 'max:32'],
        ]);

        $package = SubscriptionPackage::selectedOnceOffPackage();
        abort_if($package === null, 503, 'Memorial page pricing is not configured.');

        $transaction = $this->pendingTransaction($pamphlet, $package);
        $discountCodeService->apply($transaction, $request->string('code')->toString(), $package);
        $transaction->refresh();

        if ($transaction->amount_cents <= 0) {
            $fulfillmentService->fulfill($pamphlet, $transaction, [
                'payment_status' => 'COMPLETE',
                'source' => 'discount_code_zero',
            ]);

            return redirect()
                ->route('memorial.edit', $pamphlet)
                ->with('status', 'Discount applied. Your memorial is ready — no payment required.');
        }

        return redirect()->route('payments.checkout', $pamphlet);
    }

    public function removeDiscount(
        Request $request,
        MemorialPagePamphlet $pamphlet,
        DiscountCodeService $discountCodeService,
    ): RedirectResponse {
        abort_unless($pamphlet->isOwnedBy($request->user()), 403);

        $transaction = $pamphlet->transactions()
            ->where('status', TransactionStatus::Initiated)
            ->latest()
            ->first();

        if ($transaction !== null) {
            $discountCodeService->release($transaction);
        }

        return redirect()->route('payments.checkout', $pamphlet);
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

    public function handleCancel(
        MemorialPagePamphlet $pamphlet,
        DiscountCodeService $discountCodeService,
    ): RedirectResponse {
        abort_unless($pamphlet->isOwnedBy(request()->user()), 403);

        $transaction = $pamphlet->transactions()->latest()->first();

        if ($transaction !== null) {
            $discountCodeService->release($transaction);
            $transaction->update([
                'status' => TransactionStatus::Cancelled,
            ]);
        }

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
            app(DiscountCodeService::class)->release($transaction);

            $transaction->update([
                'provider_payment_id' => $request->string('pf_payment_id')->toString() ?: null,
                'status' => TransactionStatus::Failed,
                'raw_payload' => $payload,
            ]);
        }

        return response('OK', 200);
    }

    /**
     * @return array{
     *     checkoutUrl: string,
     *     payload: array<string, mixed>,
     *     payment_id: string,
     *     amount_cents: int,
     *     original_amount_cents: int,
     *     currency: string
     * }
     */
    private function buildPayfastCheckout(MemorialPagePamphlet $pamphlet, PayfastService $payfastService): array
    {
        $package = SubscriptionPackage::selectedOnceOffPackage();

        abort_if($package === null, 503, 'Memorial page pricing is not configured.');

        $transaction = $this->pendingTransaction($pamphlet, $package);

        return [
            'checkoutUrl' => $payfastService->checkoutUrl(),
            'payload' => $payfastService->buildCheckoutPayload($pamphlet, $transaction),
            'payment_id' => $transaction->id,
            'amount_cents' => $transaction->amount_cents,
            'original_amount_cents' => $transaction->original_amount_cents ?? $transaction->amount_cents,
            'currency' => $transaction->currency,
        ];
    }

    private function pendingTransaction(MemorialPagePamphlet $pamphlet, SubscriptionPackage $package): Transaction
    {
        $transaction = $pamphlet->transactions()
            ->where('status', TransactionStatus::Initiated)
            ->latest()
            ->first();

        if ($transaction === null) {
            return $pamphlet->transactions()->create([
                'user_id' => request()->user()->id,
                'type' => TransactionType::PamphletPurchase,
                'provider' => 'payfast',
                'merchant_reference' => (string) Str::ulid(),
                'amount_cents' => $package->price_cents,
                'currency' => $package->currency,
                'status' => TransactionStatus::Initiated,
            ]);
        }

        if ($transaction->discount_code_id === null
            && ($transaction->amount_cents !== $package->price_cents || $transaction->currency !== $package->currency)) {
            $transaction->update([
                'amount_cents' => $package->price_cents,
                'currency' => $package->currency,
            ]);
        }

        return $transaction->fresh();
    }

    /**
     * @return array<string, mixed>
     */
    private function pamphletPreviewPayload(MemorialPagePamphlet $pamphlet): array
    {
        $imagePath = $pamphlet->uploaded_image_path;

        return [
            'id' => $pamphlet->id,
            'heading' => $pamphlet->heading,
            'person_full_name' => $pamphlet->person_full_name,
            'date_of_birth' => optional($pamphlet->date_of_birth)->toDateString(),
            'date_of_passing' => optional($pamphlet->date_of_passing)->toDateString(),
            'date_format' => $pamphlet->date_format,
            'image_shape' => $pamphlet->image_shape,
            'image_crop_mode' => $pamphlet->image_crop_mode,
            'short_text' => $pamphlet->short_text,
            'uploaded_image_url' => MediaStorage::url($imagePath),
            'public_slug' => $pamphlet->public_slug,
            'status' => $pamphlet->status?->value ?? $pamphlet->status,
            'background_asset_path' => $pamphlet->background?->asset_path,
            'font_family' => $pamphlet->style?->font_family ?? 'Georgia',
            'heading_color' => $pamphlet->style?->heading_color ?? '#000000',
            'name_color' => $pamphlet->style?->name_color ?? '#000000',
            'short_text_color' => $pamphlet->style?->short_text_color ?? '#000000',
            'dates_color' => $pamphlet->style?->dates_color ?? '#000000',
            'layout' => PamphletLayout::normalize($pamphlet->style?->layout),
            'pamphlet_qr_code' => $pamphlet->memorialPage?->personOfInterest ? [
                'target_url' => route('memorial.public.show', $pamphlet->public_slug),
                'image_path' => $pamphlet->memorialPage->personOfInterest->qr_code_path,
            ] : null,
        ];
    }

    /**
     * @return array{name: string, price_cents: int, currency: string, billing_interval: string}|null
     */
    private function memorialPricingPayload(): ?array
    {
        $package = SubscriptionPackage::selectedOnceOffPackage();

        if ($package === null) {
            return null;
        }

        return $package->pricingPayload();
    }
}
