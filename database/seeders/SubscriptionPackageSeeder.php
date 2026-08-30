<?php

namespace Database\Seeders;

use App\Models\SubscriptionPackage;
use Illuminate\Database\Seeder;

class SubscriptionPackageSeeder extends Seeder
{
    public function run(): void
    {
        SubscriptionPackage::query()
            ->whereIn('slug', ['memorial-page', 'vault-monthly', 'vault-annual'])
            ->update(['is_active' => false]);

        $packages = [
            [
                'name' => 'Funeral Memorial',
                'slug' => 'funeral-memorial',
                'description' => 'A once-off digital memorial page and QR-coded funeral pamphlet for an individual service. No GPS memorial site or plaque.',
                'price_cents' => 69900,
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
                'name' => 'Memorial Legacy',
                'slug' => 'memorial-legacy',
                'description' => 'Everything in Funeral Memorial, plus a physical QR plaque installed at the memorial site with GPS coordinates.',
                'price_cents' => 149900,
                'billing_interval' => 'once_off',
                'is_featured' => true,
                'sort_order' => 2,
                'features' => [
                    'Everything in Funeral Memorial',
                    'Physical QR plaque installation',
                    'GPS memorial site coordinates',
                    'Lasting on-site tribute',
                ],
            ],
            [
                'name' => 'Living Legacy',
                'slug' => 'living-legacy',
                'description' => 'A timeboxed digital vault for messages, photos, videos, and files, handed over after your passing.',
                'price_cents' => 9900,
                'billing_interval' => 'monthly',
                'is_featured' => false,
                'sort_order' => 3,
                'features' => [
                    'Digital vault with 1GB storage',
                    'Beneficiaries with secure access codes',
                    'Photos, videos, voice notes, and documents',
                    'Messages released to loved ones',
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
