<?php

use App\Models\HelpCenterArticle;
use App\Models\HelpCenterTopic;
use App\Models\StaffUser;
use Database\Seeders\HelpCenterFaqSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('help center faq seeder creates published articles for the configured topic', function () {
    HelpCenterTopic::factory()->create([
        'id' => HelpCenterFaqSeeder::TOPIC_ID,
        'name' => 'FAQ',
        'slug' => 'faq',
    ]);

    StaffUser::factory()->create([
        'id' => HelpCenterFaqSeeder::AUTHOR_STAFF_USER_ID,
    ]);

    $this->seed(HelpCenterFaqSeeder::class);

    expect(HelpCenterArticle::query()->where('help_center_topic_id', HelpCenterFaqSeeder::TOPIC_ID)->count())
        ->toBe(17);

    $article = HelpCenterArticle::query()
        ->where('slug', 'what-is-big-tree-garden')
        ->first();

    expect($article)->not->toBeNull()
        ->and($article->author_staff_user_id)->toBe(HelpCenterFaqSeeder::AUTHOR_STAFF_USER_ID)
        ->and($article->status)->toBe(config('constants.help_center_article.status.published'))
        ->and($article->excerpt)->toContain('digital legacy platform')
        ->and($article->body)->toContain('<p>Big Tree Garden is a digital legacy platform');
});

test('help center faq seeder skips when topic is missing', function () {
    $this->seed(HelpCenterFaqSeeder::class);

    expect(HelpCenterArticle::query()->count())->toBe(0);
});
