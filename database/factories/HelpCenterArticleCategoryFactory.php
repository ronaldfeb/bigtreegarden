<?php

namespace Database\Factories;

use App\Models\HelpCenterArticle;
use App\Models\HelpCenterArticleCategory;
use App\Models\HelpCenterCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HelpCenterArticleCategory>
 */
class HelpCenterArticleCategoryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'help_center_article_id' => HelpCenterArticle::factory(),
            'help_center_category_id' => HelpCenterCategory::factory(),
        ];
    }
}
