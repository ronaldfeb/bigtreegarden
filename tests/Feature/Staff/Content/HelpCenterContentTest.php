<?php

use App\Models\HelpCenterArticle;
use App\Models\HelpCenterCategory;
use App\Models\HelpCenterTopic;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('staff can view and edit help center topics by id', function () {
    $staff = makeStaffUser();
    $topic = HelpCenterTopic::factory()->create(['name' => 'Billing']);

    $this->actingAs($staff)
        ->get(route('staff.content.help-center-topics.show', $topic->id))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('staff/content/help-center-topics/Show')
            ->where('topic.id', $topic->id));

    $this->actingAs($staff)
        ->get(route('staff.content.help-center-topics.edit', $topic->id))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('staff/content/help-center-topics/Edit')
            ->where('topic.id', $topic->id));
});

test('staff can view and edit help center topics by slug', function () {
    $staff = makeStaffUser();
    $topic = HelpCenterTopic::factory()->create(['name' => 'Account settings']);

    $this->actingAs($staff)
        ->get(route('staff.content.help-center-topics.show', $topic))
        ->assertOk();

    $this->actingAs($staff)
        ->get(route('staff.content.help-center-topics.edit', $topic))
        ->assertOk();
});

test('staff can manage help center categories', function () {
    $staff = makeStaffUser();
    $category = HelpCenterCategory::factory()->create(['name' => 'Getting started']);

    $this->actingAs($staff)
        ->get(route('staff.content.help-center-categories.show', $category))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('staff/content/help-center-categories/Show')
            ->where('category.id', $category->id));

    $this->actingAs($staff)
        ->patch(route('staff.content.help-center-categories.update', $category), [
            'name' => 'Updated category',
            'sort_order' => 2,
            'is_active' => true,
        ])
        ->assertRedirect(route('staff.content.help-center-categories.show', $category));

    expect($category->fresh()->name)->toBe('Updated category');
});

test('staff can view and edit help center articles by id', function () {
    $staff = makeStaffUser();
    $topic = HelpCenterTopic::factory()->create();
    $category = HelpCenterCategory::factory()->create();
    $article = HelpCenterArticle::factory()->create([
        'help_center_topic_id' => $topic->id,
        'title' => 'Reset your password',
    ]);
    $article->categories()->sync([$category->id]);

    $this->actingAs($staff)
        ->get(route('staff.content.help-center-articles.show', $article->id))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('staff/content/help-center-articles/Show')
            ->where('article.id', $article->id));

    $this->actingAs($staff)
        ->get(route('staff.content.help-center-articles.edit', $article->id))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('staff/content/help-center-articles/Edit')
            ->where('article.id', $article->id)
            ->has('topics')
            ->has('categories'));

    $this->actingAs($staff)
        ->patch(route('staff.content.help-center-articles.update', $article->id), [
            'title' => 'Updated article title',
            'status' => 'published',
            'category_ids' => [$category->id],
        ])
        ->assertRedirect(route('staff.content.help-center-articles.show', $article->fresh()));

    $article->refresh();

    expect($article->title)->toBe('Updated article title')
        ->and($article->categories->pluck('id')->all())->toBe([$category->id]);
});

test('staff can create help center topics and articles', function () {
    $staff = makeStaffUser();
    $category = HelpCenterCategory::factory()->create();

    $this->actingAs($staff)
        ->post(route('staff.content.help-center-topics.store'), [
            'name' => 'Memorial pages',
            'description' => 'Learn about memorial pages.',
            'sort_order' => 1,
            'is_active' => true,
        ])
        ->assertRedirect();

    $topic = HelpCenterTopic::query()->where('name', 'Memorial pages')->firstOrFail();

    $this->actingAs($staff)
        ->post(route('staff.content.help-center-articles.store'), [
            'help_center_topic_id' => $topic->id,
            'title' => 'Create a memorial page',
            'body' => 'Step-by-step instructions.',
            'status' => 'draft',
            'category_ids' => [$category->id],
        ])
        ->assertRedirect();

    $article = HelpCenterArticle::query()->where('title', 'Create a memorial page')->firstOrFail();

    expect($article->help_center_topic_id)->toBe($topic->id)
        ->and($article->categories->pluck('id')->all())->toBe([$category->id]);
});

test('staff can update help center articles without clearing published at', function () {
    $staff = makeStaffUser();
    $topic = HelpCenterTopic::factory()->create();
    $publishedAt = now()->subDay()->startOfMinute();
    $article = HelpCenterArticle::factory()->create([
        'help_center_topic_id' => $topic->id,
        'title' => 'Published article',
        'status' => 'published',
        'published_at' => $publishedAt,
    ]);

    $this->actingAs($staff)
        ->patch(route('staff.content.help-center-articles.update', $article), [
            'title' => 'Updated published article',
            'body' => '<p>Updated body</p>',
            'status' => 'published',
            'published_at' => '',
        ])
        ->assertRedirect(route('staff.content.help-center-articles.show', $article->fresh()));

    $article->refresh();

    expect($article->title)->toBe('Updated published article')
        ->and($article->published_at)->not->toBeNull()
        ->and($article->published_at->equalTo($publishedAt))->toBeTrue();
});
