<?php

namespace App\Http\Controllers;

use App\Enums\SubscriptionStatus;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Subscription;
use App\Models\SubscriptionPackage;
use App\Services\PayfastService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Laravel\Fortify\Features;

class SubscriptionController extends Controller
{
    public function store(Request $request, SubscriptionPackage $package): RedirectResponse
    {
        abort_unless($package->is_active && $package->billing_interval !== 'once_off', 404);

        $user = $request->user();

        if ($user->hasActiveSubscription()) {
            return redirect()
                ->route('subscriptions.show')
                ->with('status', 'You already have an active subscription.');
        }

        $subscription = $user->subscriptions()
            ->where('status', SubscriptionStatus::Pending)
            ->where('subscription_package_id', $package->id)
            ->latest()
            ->first();

        if ($subscription === null) {
            $subscription = $user->subscriptions()->create([
                'subscription_package_id' => $package->id,
                'status' => SubscriptionStatus::Pending,
                'merchant_reference' => (string) Str::ulid(),
            ]);
        }

        return redirect()->route('subscriptions.checkout', $subscription);
    }

    public function checkout(Request $request, Subscription $subscription, PayfastService $payfastService): InertiaResponse|RedirectResponse
    {
        abort_unless($subscription->user_id === $request->user()->id, 403);

        if ($subscription->isActive()) {
            return redirect()->route('subscriptions.show');
        }

        $transaction = $subscription->transactions()
            ->where('status', TransactionStatus::Initiated)
            ->latest()
            ->first();

        if ($transaction === null) {
            $transaction = $subscription->transactions()->create([
                'user_id' => $request->user()->id,
                'type' => TransactionType::Subscription,
                'provider' => 'payfast',
                'merchant_reference' => $subscription->merchant_reference,
                'amount_cents' => $subscription->package->price_cents,
                'currency' => $subscription->package->currency,
                'status' => TransactionStatus::Initiated,
            ]);
        }

        $subscription->loadMissing('package');
        $package = $subscription->package;

        return Inertia::render('subscriptions/Checkout', [
            'checkoutUrl' => $payfastService->checkoutUrl(),
            'payload' => $payfastService->buildSubscriptionCheckoutPayload($subscription, $transaction),
            'packageName' => $package->name,
            'amount_cents' => $package->price_cents,
            'currency' => $package->currency,
            'billing_interval' => $package->billing_interval,
            'autoSubmit' => true,
            'canRegister' => Features::enabled(Features::registration()),
        ]);
    }

    public function show(Request $request): InertiaResponse
    {
        $subscription = $request->user()->subscriptions()
            ->with('package')
            ->whereIn('status', [SubscriptionStatus::Active, SubscriptionStatus::Cancelled])
            ->latest('activated_at')
            ->first();

        return Inertia::render('subscriptions/Show', [
            'subscription' => $subscription === null ? null : [
                'id' => $subscription->id,
                'status' => $subscription->status->value,
                'package_name' => $subscription->package->name,
                'billing_interval' => $subscription->package->billing_interval,
                'price_cents' => $subscription->package->price_cents,
                'currency' => $subscription->package->currency,
                'next_billing_at' => $subscription->next_billing_at?->toDateString(),
                'activated_at' => $subscription->activated_at?->toDateString(),
                'cancelled_at' => $subscription->cancelled_at?->toDateString(),
            ],
        ]);
    }

    public function handleReturn(Request $request, Subscription $subscription): RedirectResponse
    {
        abort_unless($subscription->user_id === $request->user()->id, 403);

        $subscription->refresh();

        $message = $subscription->isActive()
            ? 'Your subscription is active. Welcome to the vault.'
            : 'Your subscription payment is being processed. This may take a moment.';

        return redirect()->route('subscriptions.show')->with('status', $message);
    }

    public function handleCancelled(Request $request, Subscription $subscription): RedirectResponse
    {
        abort_unless($subscription->user_id === $request->user()->id, 403);

        if ($subscription->status === SubscriptionStatus::Pending) {
            $subscription->transactions()
                ->where('status', TransactionStatus::Initiated)
                ->latest()
                ->first()
                ?->update(['status' => TransactionStatus::Cancelled]);
        }

        return redirect()->route('pricing')->with('status', 'Subscription checkout was cancelled.');
    }

    public function cancel(Request $request, Subscription $subscription): RedirectResponse
    {
        abort_unless($subscription->user_id === $request->user()->id, 403);
        abort_unless($subscription->isActive(), 400);

        $subscription->update([
            'status' => SubscriptionStatus::Cancelled,
            'cancelled_at' => now(),
        ]);

        return redirect()
            ->route('subscriptions.show')
            ->with('status', 'Your subscription has been cancelled. Vault access ends at the end of the paid period.');
    }

    public function handleNotify(
        Request $request,
        Subscription $subscription,
        PayfastService $payfastService
    ): Response {
        $payload = $request->all();
        $paramString = $payfastService->buildItnParameterString($payload);

        $transaction = $subscription->transactions()
            ->where('merchant_reference', $request->string('m_payment_id')->toString())
            ->first();

        if ($transaction === null) {
            Log::warning('PayFast subscription ITN received for unknown transaction.', [
                'subscription_id' => $subscription->id,
                'm_payment_id' => $request->string('m_payment_id')->toString(),
            ]);

            return response('OK', 200);
        }

        $signatureValid = $payfastService->isValidItnSignature($payload, $paramString);
        $amountValid = $payfastService->isValidItnAmount($transaction, $payload);
        $serverConfirmed = $payfastService->confirmItnWithPayfast($paramString);

        if (! $signatureValid || ! $amountValid || ! $serverConfirmed) {
            Log::warning('PayFast subscription ITN failed security checks.', [
                'subscription_id' => $subscription->id,
                'transaction_id' => $transaction->id,
                'signature_valid' => $signatureValid,
                'amount_valid' => $amountValid,
                'server_confirmed' => $serverConfirmed,
            ]);

            return response('OK', 200);
        }

        $status = $request->string('payment_status')->upper()->toString();

        if ($status !== 'COMPLETE') {
            $transaction->update([
                'provider_payment_id' => $request->string('pf_payment_id')->toString() ?: null,
                'status' => TransactionStatus::Failed,
                'raw_payload' => $payload,
            ]);

            return response('OK', 200);
        }

        $isRenewal = $transaction->status === TransactionStatus::Complete;

        if ($isRenewal) {
            $transaction = $subscription->transactions()->create([
                'user_id' => $subscription->user_id,
                'type' => TransactionType::Subscription,
                'provider' => 'payfast',
                'merchant_reference' => (string) Str::ulid(),
                'amount_cents' => $transaction->amount_cents,
                'currency' => $transaction->currency,
                'status' => TransactionStatus::Initiated,
            ]);
        }

        $transaction->update([
            'provider_payment_id' => $request->string('pf_payment_id')->toString() ?: null,
            'status' => TransactionStatus::Complete,
            'paid_at' => now(),
            'raw_payload' => $payload,
        ]);

        $interval = $subscription->package->billing_interval;

        $subscription->update([
            'status' => SubscriptionStatus::Active,
            'payfast_token' => $request->string('token')->toString() ?: $subscription->payfast_token,
            'activated_at' => $subscription->activated_at ?? now(),
            'next_billing_at' => $interval === 'annual' ? now()->addYear() : now()->addMonth(),
        ]);

        return response('OK', 200);
    }
}
