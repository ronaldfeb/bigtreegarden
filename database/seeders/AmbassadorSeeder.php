<?php

namespace Database\Seeders;

use App\Models\Ambassador;
use Illuminate\Database\Seeder;

class AmbassadorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ambassadors = [
            [
                'name' => 'Margaret van der Berg',
                'title' => 'Community liaison',
                'description' => 'Margaret helps families navigate memorial planning with warmth and practical guidance built over twenty years of community work.',
                'image_url' => 'https://placehold.net/400x400.svg',
                'status' => config('constants.ambassador.status.active'),
                'handle_linkedin' => 'https://linkedin.com/in/margaret-van-der-berg',
                'website_url' => 'https://example.com/margaret',
            ],
            [
                'name' => 'James Okonkwo',
                'title' => 'Pastoral care partner',
                'description' => 'James supports congregations and families in creating meaningful tributes that honour faith and family traditions.',
                'image_url' => 'https://placehold.net/400x400.svg',
                'status' => config('constants.ambassador.status.active'),
                'handle_instagram' => 'https://instagram.com/jamesokonkwo',
            ],
            [
                'name' => 'Sarah Mitchell',
                'title' => 'Family advocate',
                'description' => null,
                'image_url' => 'https://placehold.net/400x400.svg',
                'status' => config('constants.ambassador.status.active'),
            ],
            [
                'name' => 'David Chen',
                'title' => 'Former ambassador',
                'description' => 'No longer active on the programme.',
                'image_url' => 'https://placehold.net/400x400.svg',
                'status' => config('constants.ambassador.status.inactive'),
            ],
        ];

        foreach ($ambassadors as $ambassador) {
            Ambassador::query()->create($ambassador);
        }
    }
}
