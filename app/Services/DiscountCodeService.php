<?php

namespace App\Services;

use App\Models\DiscountCode;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DiscountCodeService
{
    /**
     * @throws ValidationException
     */
    public function findUsable(string $rawCode, Model $target, ?Transaction $transaction = null): DiscountCode
    {
        $normalized = DiscountCode::normalize($rawCode);

        if (strlen($normalized) !== 8) {
            throw ValidationException::withMessages([
                'code' => 'Enter a valid discount code in the format 0A-YO1B-88.',
            ]);
        }

        $discountCode = DiscountCode::query()->where('code', $normalized)->first();

        if ($discountCode === null) {
            throw ValidationException::withMessages([
                'code' => 'This discount code was not found.',
            ]);
        }

        if (! $discountCode->isUsable($transaction)) {
            throw ValidationException::withMessages([
                'code' => 'This discount code is not available.',
            ]);
        }

        if (! $discountCode->appliesTo($target)) {
            throw ValidationException::withMessages([
                'code' => 'This discount code does not apply to this purchase.',
            ]);
        }

        return $discountCode;
    }

    /**
     * @throws ValidationException
     */
    public function apply(Transaction $transaction, string $rawCode, Model $target, bool $allowZeroPayable = true): DiscountCode
    {
        return DB::transaction(function () use ($transaction, $rawCode, $target, $allowZeroPayable): DiscountCode {
            $transaction = Transaction::query()->lockForUpdate()->findOrFail($transaction->id);

            if ($transaction->discount_code_id !== null) {
                $this->release($transaction->fresh());
                $transaction->refresh();
            }

            $discountCode = DiscountCode::query()
                ->where('code', DiscountCode::normalize($rawCode))
                ->lockForUpdate()
                ->first();

            if ($discountCode === null) {
                throw ValidationException::withMessages([
                    'code' => 'This discount code was not found.',
                ]);
            }

            if (! $discountCode->isUsable($transaction)) {
                throw ValidationException::withMessages([
                    'code' => 'This discount code is not available.',
                ]);
            }

            if (! $discountCode->appliesTo($target)) {
                throw ValidationException::withMessages([
                    'code' => 'This discount code does not apply to this purchase.',
                ]);
            }

            $listPrice = $transaction->original_amount_cents ?? $transaction->amount_cents;
            $payable = $discountCode->payableCents($listPrice);

            if (! $allowZeroPayable && $payable <= 0) {
                throw ValidationException::withMessages([
                    'code' => 'This discount cannot fully cover a subscription. Choose a smaller discount.',
                ]);
            }

            $discountCode->update([
                'reserved_transaction_id' => $transaction->id,
                'reserved_at' => now(),
            ]);

            $transaction->update([
                'discount_code_id' => $discountCode->id,
                'original_amount_cents' => $listPrice,
                'amount_cents' => $payable,
            ]);

            return $discountCode->fresh();
        });
    }

    public function release(Transaction $transaction): void
    {
        DB::transaction(function () use ($transaction): void {
            $transaction = Transaction::query()->lockForUpdate()->find($transaction->id);

            if ($transaction === null || $transaction->discount_code_id === null) {
                return;
            }

            $discountCode = DiscountCode::query()
                ->whereKey($transaction->discount_code_id)
                ->lockForUpdate()
                ->first();

            if ($discountCode !== null
                && $discountCode->used_at === null
                && $discountCode->reserved_transaction_id === $transaction->id) {
                $discountCode->update([
                    'reserved_transaction_id' => null,
                    'reserved_at' => null,
                ]);
            }

            $listPrice = $transaction->original_amount_cents ?? $transaction->amount_cents;

            $transaction->update([
                'discount_code_id' => null,
                'original_amount_cents' => null,
                'amount_cents' => $listPrice,
            ]);
        });
    }

    public function consume(Transaction $transaction, ?User $user = null): void
    {
        DB::transaction(function () use ($transaction, $user): void {
            $transaction = Transaction::query()->lockForUpdate()->find($transaction->id);

            if ($transaction === null || $transaction->discount_code_id === null) {
                return;
            }

            $discountCode = DiscountCode::query()
                ->whereKey($transaction->discount_code_id)
                ->lockForUpdate()
                ->first();

            if ($discountCode === null || $discountCode->used_at !== null) {
                return;
            }

            $discountCode->update([
                'used_at' => now(),
                'used_by_user_id' => $user?->id ?? $transaction->user_id,
                'used_transaction_id' => $transaction->id,
                'reserved_transaction_id' => null,
                'reserved_at' => null,
            ]);
        });
    }

    /**
     * @return array{code: string, formatted_code: string, discount_cents: int, amount_cents: int, original_amount_cents: int}|null
     */
    public function checkoutPayload(?Transaction $transaction): ?array
    {
        if ($transaction === null || $transaction->discount_code_id === null) {
            return null;
        }

        $discountCode = $transaction->discountCode;

        if ($discountCode === null) {
            return null;
        }

        $original = $transaction->original_amount_cents ?? $transaction->amount_cents;

        return [
            'code' => $discountCode->code,
            'formatted_code' => $discountCode->formattedCode(),
            'discount_cents' => max(0, $original - $transaction->amount_cents),
            'amount_cents' => $transaction->amount_cents,
            'original_amount_cents' => $original,
        ];
    }
}
