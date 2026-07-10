<?php

namespace Database\Seeders;

use App\Models\BlogCategory;
use App\Models\HelpCenterTopic;
use App\Models\Policy;
use Illuminate\Database\Seeder;

class ContentSeeder extends Seeder
{
    public function run(): void
    {
        Policy::query()->updateOrCreate(
            ['type' => 'terms_of_service'],
            [
                'title' => 'Terms of Service',
                'body' => '<p>Terms of service content.</p>',
                'version' => 'v1.0',
                'published_at' => now(),
            ],
        );

        Policy::query()->updateOrCreate(
            ['type' => 'privacy_policy'],
            [
                'title' => 'Privacy Policy',
                'body' => '<p>Privacy policy content.</p>',
                'version' => 'v1.0',
                'published_at' => now(),
            ],
        );

        Policy::query()->updateOrCreate(
            ['type' => 'about_us'],
            [
                'title' => 'About Us',
                'body' => '<p>About BigTreeGarden.</p>',
                'version' => 'v1.0',
                'published_at' => now(),
            ],
        );

        BlogCategory::query()->firstOrCreate(
            ['slug' => 'general'],
            [
                'name' => 'General',
                'description' => 'General blog posts.',
                'sort_order' => 0,
                'is_active' => true,
            ],
        );

        HelpCenterTopic::query()->firstOrCreate(
            ['slug' => 'getting-started'],
            [
                'name' => 'Getting Started',
                'description' => 'Help for new users.',
                'icon' => 'circle-help',
                'sort_order' => 0,
                'is_active' => true,
            ],
        );
    }
}
