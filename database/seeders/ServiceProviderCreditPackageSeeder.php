<?php

namespace Database\Seeders;

use App\Models\ServiceProviderCreditPackage;
use Illuminate\Database\Seeder;

class ServiceProviderCreditPackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            [
                'name' => 'Starter pack',
                'slug' => 'starter-pack-5',
                'description' => '5 memorial pages at a wholesale rate.',
                'page_count' => 5,
                'price_cents' => 299500,
                'sort_order' => 1,
            ],
            [
                'name' => 'Growth pack',
                'slug' => 'growth-pack-10',
                'description' => '10 memorial pages for busy parlours.',
                'page_count' => 10,
                'price_cents' => 549000,
                'sort_order' => 2,
            ],
            [
                'name' => 'Volume pack',
                'slug' => 'volume-pack-25',
                'description' => '25 memorial pages with the best unit price.',
                'page_count' => 25,
                'price_cents' => 1247500,
                'sort_order' => 3,
            ],
        ];

        foreach ($packages as $package) {
            ServiceProviderCreditPackage::query()->updateOrCreate(
                ['slug' => $package['slug']],
                [
                    ...$package,
                    'currency' => 'ZAR',
                    'is_active' => true,
                ],
            );
        }
    }
}
