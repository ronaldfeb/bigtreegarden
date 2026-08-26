<?php

namespace Database\Factories;

use App\Enums\ServiceProviderCreditPaymentMethod;
use App\Enums\ServiceProviderCreditPurchaseStatus;
use App\Models\ServiceProvider;
use App\Models\ServiceProviderCreditPackage;
use App\Models\ServiceProviderCreditPurchase;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ServiceProviderCreditPurchase>
 */
class ServiceProviderCreditPurchaseFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $package = ServiceProviderCreditPackage::factory()->create();

        return [
            'service_provider_id' => ServiceProvider::factory(),
            'service_provider_credit_package_id' => $package->id,
            'purchased_by_user_id' => User::factory(),
            'package_name' => $package->name,
            'page_count' => $package->page_count,
            'price_cents' => $package->price_cents,
            'currency' => $package->currency,
            'payment_method' => ServiceProviderCreditPaymentMethod::Payfast,
            'status' => ServiceProviderCreditPurchaseStatus::PendingPayment,
            'payment_reference' => 'SP-'.Str::upper(Str::random(12)),
            'proof_of_payment_path' => null,
            'reviewed_by_staff_user_id' => null,
            'reviewed_at' => null,
            'review_note' => null,
            'released_at' => null,
        ];
    }

    public function bankTransfer(): static
    {
        return $this->state(fn (array $attributes): array => [
            'payment_method' => ServiceProviderCreditPaymentMethod::BankTransfer,
            'status' => ServiceProviderCreditPurchaseStatus::PendingReview,
            'proof_of_payment_path' => 'providers/pop/'.fake()->uuid().'.pdf',
        ]);
    }

    public function released(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ServiceProviderCreditPurchaseStatus::Released,
            'released_at' => now(),
        ]);
    }
}
