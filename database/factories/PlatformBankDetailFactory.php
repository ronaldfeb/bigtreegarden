<?php

namespace Database\Factories;

use App\Models\PlatformBankDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlatformBankDetail>
 */
class PlatformBankDetailFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'bank_name' => 'FNB',
            'account_name' => 'BigTreeGarden (Pty) Ltd',
            'account_number' => fake()->numerify('##########'),
            'branch_code' => '250655',
            'reference_note' => 'Please use the payment reference provided at checkout.',
            'is_active' => true,
        ];
    }
}
