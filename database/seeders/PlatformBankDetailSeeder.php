<?php

namespace Database\Seeders;

use App\Models\PlatformBankDetail;
use Illuminate\Database\Seeder;

class PlatformBankDetailSeeder extends Seeder
{
    public function run(): void
    {
        if (PlatformBankDetail::current() !== null) {
            return;
        }

        PlatformBankDetail::query()->create([
            'bank_name' => 'FNB',
            'account_name' => 'BigTreeGarden (Pty) Ltd',
            'account_number' => '62801234567',
            'branch_code' => '250655',
            'reference_note' => 'Please use the unique payment reference shown at checkout as your EFT reference.',
            'is_active' => true,
        ]);
    }
}
