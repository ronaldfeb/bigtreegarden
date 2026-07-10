<?php

namespace Database\Seeders;

use App\Models\SubscriptionPackage;
use Illuminate\Database\Seeder;

class SubscriptionPackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            [
                'name' => 'Memorial Page',
                'slug' => 'memorial-page',
                'description' => 'A once-off memorial page with QR-coded pamphlet.',
                'price_cents' => (int) config('memorial.fixed_price_cents', 69900),
                'billing_interval' => 'once_off',
                'is_featured' => false,
                'sort_order' => 1,
                'features' => [
                    'Personalised memorial page',
                    'Printable QR-coded pamphlet',
                    'Photo gallery',
                    'Guest tributes',
                ],
            ],
            [
                'name' => 'Vault Monthly',
                'slug' => 'vault-monthly',
                'description' => 'Preserve memories, media, and messages for your loved ones.',
                'price_cents' => 9900,
                'billing_interval' => 'monthly',
                'is_featured' => true,
                'sort_order' => 2,
                'features' => [
                    'Digital vault with 1GB storage',
                    'Beneficiaries with secure access codes',
                    'Photos, videos, voice notes, and documents',
                    'Messages released to loved ones',
                ],
            ],
            [
                'name' => 'Vault Annual',
                'slug' => 'vault-annual',
                'description' => 'The full vault experience, billed once a year.',
                'price_cents' => 99900,
                'billing_interval' => 'annual',
                'is_featured' => false,
                'sort_order' => 3,
                'features' => [
                    'Everything in Vault Monthly',
                    'Two months free',
                    'Priority support',
                ],
            ],
        ];

        foreach ($packages as $definition) {
            $features = $definition['features'];
            unset($definition['features']);

            $package = SubscriptionPackage::query()->updateOrCreate(
                ['slug' => $definition['slug']],
                [...$definition, 'currency' => 'ZAR', 'is_active' => true],
            );

            foreach ($features as $index => $label) {
                $package->features()->updateOrCreate(
                    ['label' => $label],
                    ['is_included' => true, 'sort_order' => $index],
                );
            }
        }
    }
}
