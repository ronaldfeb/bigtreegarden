<?php

namespace App\Services;

use App\Models\Pamphlet;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;

class PayfastService
{
    /**
     * @return array<string, scalar|null>
     */
    public function buildCheckoutPayload(Pamphlet $pamphlet, Payment $payment): array
    {
        $itemName = sprintf('Memorial pamphlet for %s', $pamphlet->person_full_name);
        $amount = number_format(config('memorial.fixed_price_cents') / 100, 2, '.', '');
        [$nameFirst, $nameLast] = $this->splitName($pamphlet->user->name);

        $data = [
            'merchant_id' => config('services.payfast.merchant_id'),
            'merchant_key' => config('services.payfast.merchant_key'),
            'return_url' => route('payments.return', $pamphlet),
            'cancel_url' => route('payments.cancel', $pamphlet),
            'notify_url' => route('payments.notify', $pamphlet),
            'name_first' => $nameFirst,
            'name_last' => $nameLast,
            'email_address' => $pamphlet->user->email,
            'm_payment_id' => (string) $payment->id,
            'amount' => $amount,
            'item_name' => $itemName,
        ];

        $data['signature'] = $this->generateSignature($data, $this->passphrase());

        return $data;
    }

    public function checkoutUrl(): string
    {
        return rtrim((string) config('services.payfast.url'), '/');
    }

    /**
     * @param  array<string, scalar|null>  $data
     */
    public function generateSignature(array $data, ?string $passphrase = null): string
    {
        $parameterString = '';

        foreach ($data as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $parameterString .= $key.'='.urlencode(trim((string) $value)).'&';
        }

        $parameterString = rtrim($parameterString, '&');

        if ($passphrase !== null && $passphrase !== '') {
            $parameterString .= '&passphrase='.urlencode(trim($passphrase));
        }

        return md5($parameterString);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function buildItnParameterString(array $data): string
    {
        $parameterString = '';

        foreach ($data as $key => $value) {
            if ($key === 'signature') {
                break;
            }

            $parameterString .= $key.'='.urlencode(stripslashes((string) $value)).'&';
        }

        return rtrim($parameterString, '&');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function isValidItnSignature(array $data, string $paramString): bool
    {
        if (! isset($data['signature'])) {
            return false;
        }

        $passphrase = $this->passphrase();
        $tempParamString = $paramString;

        if ($passphrase !== null && $passphrase !== '') {
            $tempParamString .= '&passphrase='.urlencode($passphrase);
        }

        return hash_equals((string) $data['signature'], md5($tempParamString));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function isValidItnAmount(Payment $payment, array $data): bool
    {
        if (! isset($data['amount_gross'])) {
            return false;
        }

        $expectedAmount = $payment->amount_cents / 100;

        return abs($expectedAmount - (float) $data['amount_gross']) <= 0.01;
    }

    public function confirmItnWithPayfast(string $paramString): bool
    {
        $host = $this->validationHost();
        $response = Http::withBody($paramString, 'application/x-www-form-urlencoded')
            ->withOptions([
                'verify' => true,
            ])
            ->post("https://{$host}/eng/query/validate");

        return $response->successful() && trim($response->body()) === 'VALID';
    }

    public function validationHost(): string
    {
        $host = parse_url($this->checkoutUrl(), PHP_URL_HOST);

        return is_string($host) && $host !== '' ? $host : 'sandbox.payfast.co.za';
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function splitName(string $name): array
    {
        $trimmedName = trim($name);

        if ($trimmedName === '') {
            return ['', ''];
        }

        $parts = preg_split('/\s+/', $trimmedName, 2);

        return [
            $parts[0] ?? '',
            $parts[1] ?? '',
        ];
    }

    private function passphrase(): ?string
    {
        $passphrase = config('services.payfast.passphrase');

        return is_string($passphrase) && $passphrase !== '' ? $passphrase : null;
    }
}
