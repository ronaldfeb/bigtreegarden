<?php

namespace Database\Factories;

use App\Models\Pamphlet;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'pamphlet_id' => Pamphlet::factory(),
            'provider' => 'payfast',
            'provider_payment_id' => null,
            'amount_cents' => 69900,
            'currency' => 'ZAR',
            'status' => 'initiated',
            'paid_at' => null,
            'raw_payload' => null,
        ];
    }
}
