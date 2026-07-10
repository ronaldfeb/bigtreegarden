<?php

namespace Database\Factories;

use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\StaffUser;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Blog>
 */
class BlogFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'blog_category_id' => BlogCategory::factory(),
            'author_staff_user_id' => StaffUser::factory(),
            'title' => fake()->sentence(4),
            'slug' => fake()->unique()->slug(),
            'excerpt' => fake()->optional()->paragraph(),
            'body' => fake()->paragraphs(4, true),
            'cover_image_path' => null,
            'status' => 'draft',
            'published_at' => null,
            'meta_title' => null,
            'meta_description' => null,
            'view_count' => 0,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => config('constants.blog.status.published'),
            'published_at' => now()->subDay(),
        ]);
    }
}
