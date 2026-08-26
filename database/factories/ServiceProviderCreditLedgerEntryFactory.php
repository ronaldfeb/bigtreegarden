<?php

namespace Database\Factories;

use App\Models\ServiceProvider;
use App\Models\ServiceProviderCreditLedgerEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceProviderCreditLedgerEntry>
 */
class ServiceProviderCreditLedgerEntryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $delta = fake()->randomElement([5, 10, 25, -1]);

        return [
            'service_provider_id' => ServiceProvider::factory(),
            'delta' => $delta,
            'balance_after' => max(0, $delta),
            'reason' => $delta > 0 ? 'purchase_released' : 'memorial_created',
            'reference_type' => null,
            'reference_id' => null,
            'created_by_user_id' => User::factory(),
        ];
    }
}
