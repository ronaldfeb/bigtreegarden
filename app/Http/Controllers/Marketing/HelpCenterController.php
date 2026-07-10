<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\HelpCenterArticle;
use App\Models\HelpCenterTopic;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

class HelpCenterController extends Controller
{
    public function index(): Response
    {
        $topics = HelpCenterTopic::query()
            ->active()
            ->withCount(['articles as published_articles_count' => fn ($query) => $query->published()])
            ->orderBy('sort_order')
            ->get(['id', 'name', 'slug', 'description', 'icon'])
            ->map(fn (HelpCenterTopic $topic): array => [
                'id' => $topic->id,
                'name' => $topic->name,
                'slug' => $topic->slug,
                'description' => $topic->description,
                'icon' => $topic->icon,
                'published_articles_count' => $topic->published_articles_count,
            ]);

        return Inertia::render('marketing/Help/Index', [
            'canRegister' => Features::enabled(Features::registration()),
            'topics' => $topics,
        ]);
    }

    public function topic(HelpCenterTopic $helpCenterTopic): Response
    {
        abort_unless($helpCenterTopic->is_active, 404);

        $articles = $helpCenterTopic->articles()
            ->published()
            ->orderBy('sort_order')
            ->get(['id', 'title', 'slug', 'excerpt', 'published_at'])
            ->map(fn (HelpCenterArticle $article): array => [
                'id' => $article->id,
                'title' => $article->title,
                'slug' => $article->slug,
                'excerpt' => $article->excerpt,
                'published_at' => $article->published_at?->toIso8601String(),
            ]);

        return Inertia::render('marketing/Help/Topic', [
            'canRegister' => Features::enabled(Features::registration()),
            'topic' => [
                'id' => $helpCenterTopic->id,
                'name' => $helpCenterTopic->name,
                'slug' => $helpCenterTopic->slug,
                'description' => $helpCenterTopic->description,
                'icon' => $helpCenterTopic->icon,
            ],
            'articles' => $articles,
        ]);
    }

    public function article(HelpCenterTopic $helpCenterTopic, HelpCenterArticle $helpCenterArticle): Response
    {
        abort_unless($helpCenterTopic->is_active, 404);

        abort_unless(
            $helpCenterArticle->help_center_topic_id === $helpCenterTopic->id
            && $helpCenterArticle->status === config('constants.help_center_article.status.published')
            && $helpCenterArticle->published_at !== null
            && $helpCenterArticle->published_at->lte(now()),
            404,
        );

        return Inertia::render('marketing/Help/Article', [
            'canRegister' => Features::enabled(Features::registration()),
            'topic' => [
                'id' => $helpCenterTopic->id,
                'name' => $helpCenterTopic->name,
                'slug' => $helpCenterTopic->slug,
            ],
            'article' => [
                'id' => $helpCenterArticle->id,
                'title' => $helpCenterArticle->title,
                'slug' => $helpCenterArticle->slug,
                'excerpt' => $helpCenterArticle->excerpt,
                'body' => $helpCenterArticle->body,
                'published_at' => $helpCenterArticle->published_at?->toIso8601String(),
            ],
        ]);
    }
}
