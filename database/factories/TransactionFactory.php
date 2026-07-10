<?php

namespace Database\Factories;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\MemorialPagePamphlet;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'payable_type' => MemorialPagePamphlet::class,
            'payable_id' => MemorialPagePamphlet::factory(),
            'type' => TransactionType::PamphletPurchase,
            'provider' => 'payfast',
            'merchant_reference' => Str::lower((string) Str::ulid()),
            'provider_payment_id' => null,
            'amount_cents' => 5000,
            'currency' => 'ZAR',
            'status' => TransactionStatus::Initiated,
            'paid_at' => null,
            'raw_payload' => null,
        ];
    }
}
