<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            ContentSeeder::class,
            SubscriptionPackageSeeder::class,
            TestimonialSeeder::class,
            PartnerSeeder::class,
            BackgroundCatalogSeeder::class,
            AmbassadorSeeder::class,
            DemoPersonasSeeder::class,
            StaffTeamSeeder::class,
            CrmDemoSeeder::class,
        ]);
    }
}
