<?php

namespace Database\Factories;

use App\Enums\DiscountType;
use App\Models\DiscountCode;
use App\Models\SubscriptionPackage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DiscountCode>
 */
class DiscountCodeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => DiscountCode::generate(),
            'discount_type' => DiscountType::Percent,
            'percent' => 10,
            'amount_cents' => null,
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addDays(30),
            'applies_to_all' => true,
            'discountable_type' => null,
            'discountable_id' => null,
        ];
    }

    public function fixed(int $amountCents = 5000): static
    {
        return $this->state(fn (array $attributes): array => [
            'discount_type' => DiscountType::Fixed,
            'percent' => null,
            'amount_cents' => $amountCents,
        ]);
    }

    public function forPackage(?SubscriptionPackage $package = null): static
    {
        $package ??= SubscriptionPackage::factory()->create();

        return $this->state(fn (array $attributes): array => [
            'applies_to_all' => false,
            'discountable_type' => $package::class,
            'discountable_id' => $package->id,
        ]);
    }

    public function used(): static
    {
        return $this->state(fn (array $attributes): array => [
            'used_at' => now(),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes): array => [
            'starts_at' => now()->subDays(10),
            'ends_at' => now()->subDay(),
        ]);
    }
}
