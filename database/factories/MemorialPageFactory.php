<?php

namespace Database\Factories;

use App\Enums\MemorialPageStatus;
use App\Models\MemorialPage;
use App\Models\PersonOfInterest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<MemorialPage>
 */
class MemorialPageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'person_of_interest_id' => PersonOfInterest::factory()->hasAttached(
                User::factory(),
                ['role' => 'owner'],
            ),
            'title' => fake()->sentence(3),
            'public_slug' => Str::lower((string) Str::ulid()),
            'status' => MemorialPageStatus::Draft,
            'active_day_type' => null,
            'active_day_date' => null,
            'gallery_enabled' => true,
            'live_comments_enabled' => true,
            'published_at' => null,
        ];
    }
}
