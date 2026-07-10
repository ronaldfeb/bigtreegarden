<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class TestimonialSeeder extends Seeder
{
    public function run(): void
    {
        $testimonials = [
            [
                'name' => 'Nomsa Dlamini',
                'photo_path' => $this->avatarUrl('Nomsa Dlamini'),
                'role_or_location' => 'Johannesburg',
                'body' => 'Creating my father\'s memorial page gave our whole family a place to gather stories and photos we would have lost. The pamphlet with the QR code at the service was beautiful.',
                'rating' => 5,
                'sort_order' => 0,
            ],
            [
                'name' => 'Pieter van der Merwe',
                'photo_path' => $this->avatarUrl('Pieter van der Merwe'),
                'role_or_location' => 'Cape Town',
                'body' => 'We printed the memorial pamphlet for the funeral and guests could scan the code to leave messages on the day. It made a difficult time feel a little more connected.',
                'rating' => 5,
                'sort_order' => 1,
            ],
            [
                'name' => 'Thandi Mokoena',
                'photo_path' => $this->avatarUrl('Thandi Mokoena'),
                'role_or_location' => 'Durban',
                'body' => 'BigTreeGarden helped us honour my grandmother with dignity. The page is still there for my children to visit years from now — that means everything to us.',
                'rating' => 5,
                'sort_order' => 2,
            ],
        ];

        foreach ($testimonials as $testimonial) {
            Testimonial::query()->updateOrCreate(
                ['name' => $testimonial['name']],
                [
                    ...$testimonial,
                    'status' => config('constants.testimonial.status.published'),
                    'is_featured' => true,
                ],
            );
        }
    }

    private function avatarUrl(string $name): string
    {
        return 'https://ui-avatars.com/api/?'.http_build_query([
            'name' => $name,
            'size' => 128,
            'background' => '748f3a',
            'color' => 'fbf8f2',
            'bold' => 'true',
        ]);
    }
}
