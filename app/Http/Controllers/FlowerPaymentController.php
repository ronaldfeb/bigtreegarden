<?php

namespace App\Http\Controllers;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\MemorialPageMessage;
use App\Models\Transaction;
use App\Services\PayfastService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class FlowerPaymentController extends Controller
{
    public function checkout(MemorialPageMessage $message, PayfastService $payfastService): InertiaResponse|RedirectResponse
    {
        abort_unless(request()->user()?->is($message->authorUser), 403);

        if ($this->hasCompleteTransaction($message)) {
            return redirect()
                ->route('memorial.public.show', $message->memorialPage->public_slug)
                ->with('status', 'Your flowers have been paid for and are awaiting approval from the family.');
        }

        $transaction = Transaction::query()
            ->where('payable_type', MemorialPageMessage::class)
            ->where('payable_id', $message->id)
            ->where('status', TransactionStatus::Initiated)
            ->latest()
            ->first();

        if ($transaction === null) {
            $transaction = Transaction::query()->create([
                'user_id' => request()->user()->id,
                'payable_type' => MemorialPageMessage::class,
                'payable_id' => $message->id,
                'type' => TransactionType::FlowerMessage,
                'provider' => 'payfast',
                'merchant_reference' => (string) Str::ulid(),
                'amount_cents' => config('memorial.flower_price_cents'),
                'currency' => 'ZAR',
                'status' => TransactionStatus::Initiated,
            ]);

            $message->update(['transaction_id' => $transaction->id]);
        }

        return Inertia::render('payments/FlowerCheckout', [
            'checkoutUrl' => $payfastService->checkoutUrl(),
            'payload' => $payfastService->buildFlowerCheckoutPayload($message, $transaction),
            'paymentId' => $transaction->id,
        ]);
    }

    public function handleReturn(MemorialPageMessage $message): RedirectResponse
    {
        abort_unless(request()->user()?->is($message->authorUser), 403);

        return redirect()
            ->route('memorial.public.show', $message->memorialPage->public_slug)
            ->with('status', 'Your flowers are awaiting approval from the family and will appear on the memorial page once approved.');
    }

    public function handleCancelled(MemorialPageMessage $message): RedirectResponse
    {
        abort_unless(request()->user()?->is($message->authorUser), 403);

        $publicSlug = $message->memorialPage->public_slug;

        Transaction::query()
            ->where('payable_type', MemorialPageMessage::class)
            ->where('payable_id', $message->id)
            ->where('status', TransactionStatus::Initiated)
            ->update(['status' => TransactionStatus::Cancelled]);

        if (! $this->hasCompleteTransaction($message)) {
            $message->delete();
        }

        return redirect()
            ->route('memorial.public.show', $publicSlug)
            ->with('status', 'Your flowers were cancelled. No payment was taken.');
    }

    public function handleNotify(
        Request $request,
        MemorialPageMessage $message,
        PayfastService $payfastService
    ): Response {
        $payload = $request->all();
        $paramString = $payfastService->buildItnParameterString($payload);

        $transaction = Transaction::query()
            ->where('payable_type', MemorialPageMessage::class)
            ->where('payable_id', $message->id)
            ->where('merchant_reference', $request->string('m_payment_id')->toString())
            ->first();

        if ($transaction === null) {
            Log::warning('PayFast flower ITN received for unknown transaction.', [
                'message_id' => $message->id,
                'm_payment_id' => $request->string('m_payment_id')->toString(),
            ]);

            return response('OK', 200);
        }

        $signatureValid = $payfastService->isValidItnSignature($payload, $paramString);
        $amountValid = $payfastService->isValidItnAmount($transaction, $payload);
        $serverConfirmed = $payfastService->confirmItnWithPayfast($paramString);

        if (! $signatureValid || ! $amountValid || ! $serverConfirmed) {
            Log::warning('PayFast flower ITN failed security checks.', [
                'message_id' => $message->id,
                'transaction_id' => $transaction->id,
                'signature_valid' => $signatureValid,
                'amount_valid' => $amountValid,
                'server_confirmed' => $serverConfirmed,
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
        } else {
            $transaction->update([
                'provider_payment_id' => $request->string('pf_payment_id')->toString() ?: null,
                'status' => TransactionStatus::Failed,
                'raw_payload' => $payload,
            ]);
        }

        return response('OK', 200);
    }

    private function hasCompleteTransaction(MemorialPageMessage $message): bool
    {
        return Transaction::query()
            ->where('payable_type', MemorialPageMessage::class)
            ->where('payable_id', $message->id)
            ->where('status', TransactionStatus::Complete)
            ->exists();
    }
}
