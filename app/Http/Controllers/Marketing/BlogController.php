<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

class BlogController extends Controller
{
    public function index(): Response
    {
        $blogs = Blog::query()
            ->published()
            ->with('category:id,name,slug')
            ->orderByDesc('published_at')
            ->get(['id', 'title', 'slug', 'excerpt', 'cover_image_path', 'published_at', 'blog_category_id'])
            ->map(fn (Blog $blog): array => [
                'id' => $blog->id,
                'title' => $blog->title,
                'slug' => $blog->slug,
                'excerpt' => $blog->excerpt,
                'cover_image_path' => $blog->cover_image_path,
                'published_at' => $blog->published_at?->toIso8601String(),
                'category' => $blog->category ? [
                    'id' => $blog->category->id,
                    'name' => $blog->category->name,
                    'slug' => $blog->category->slug,
                ] : null,
            ]);

        return Inertia::render('marketing/Blog/Index', [
            'canRegister' => Features::enabled(Features::registration()),
            'blogs' => $blogs,
        ]);
    }

    public function show(Blog $blog): Response
    {
        abort_unless(
            $blog->status === config('constants.blog.status.published')
            && $blog->published_at !== null
            && $blog->published_at->lte(now()),
            404,
        );

        $blog->load('category:id,name,slug');

        return Inertia::render('marketing/Blog/Show', [
            'canRegister' => Features::enabled(Features::registration()),
            'blog' => [
                'id' => $blog->id,
                'title' => $blog->title,
                'slug' => $blog->slug,
                'excerpt' => $blog->excerpt,
                'body' => $blog->body,
                'cover_image_path' => $blog->cover_image_path,
                'published_at' => $blog->published_at?->toIso8601String(),
                'meta_title' => $blog->meta_title,
                'meta_description' => $blog->meta_description,
                'category' => $blog->category ? [
                    'id' => $blog->category->id,
                    'name' => $blog->category->name,
                    'slug' => $blog->category->slug,
                ] : null,
            ],
        ]);
    }
}
