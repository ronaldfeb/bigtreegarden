<?php

namespace App\Services;

use App\Models\Pamphlet;

class PayfastService
{
    /**
     * @return array<string, scalar|null>
     */
    public function buildCheckoutPayload(Pamphlet $pamphlet): array
    {
        $itemName = sprintf('Memorial pamphlet for %s', $pamphlet->person_full_name);
        $amount = number_format(config('memorial.fixed_price_cents') / 100, 2, '.', '');

        return [
            'merchant_id' => config('services.payfast.merchant_id'),
            'merchant_key' => config('services.payfast.merchant_key'),
            'return_url' => route('payments.return', $pamphlet),
            'cancel_url' => route('payments.cancel', $pamphlet),
            'notify_url' => route('payments.notify', $pamphlet),
            'name_first' => $pamphlet->user->name,
            'email_address' => $pamphlet->user->email,
            'm_payment_id' => (string) $pamphlet->id,
            'amount' => $amount,
            'item_name' => $itemName,
        ];
    }

    public function checkoutUrl(): string
    {
        return rtrim((string) config('services.payfast.checkout_url'), '/');
    }
}
