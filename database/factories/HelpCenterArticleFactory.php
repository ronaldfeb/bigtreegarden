<?php

namespace Database\Factories;

use App\Models\HelpCenterArticle;
use App\Models\HelpCenterTopic;
use App\Models\StaffUser;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HelpCenterArticle>
 */
class HelpCenterArticleFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'help_center_topic_id' => HelpCenterTopic::factory(),
            'author_staff_user_id' => StaffUser::factory(),
            'title' => fake()->sentence(4),
            'slug' => fake()->unique()->slug(),
            'excerpt' => fake()->optional()->paragraph(),
            'body' => fake()->paragraphs(3, true),
            'status' => 'draft',
            'published_at' => null,
            'view_count' => 0,
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => config('constants.help_center_article.status.published'),
            'published_at' => now()->subDay(),
        ]);
    }
}
