<?php

namespace App\Http\Controllers\Staff\Content;

use App\Http\Controllers\Staff\Controller;
use App\Http\Requests\Staff\Content\StoreBlogCategoryRequest;
use App\Http\Requests\Staff\Content\UpdateBlogCategoryRequest;
use App\Models\BlogCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class BlogCategoryController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('manage-content');

        return Inertia::render('staff/content/blog-categories/Index', [
            'categories' => BlogCategory::query()->orderBy('sort_order')->paginate(20),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('manage-content');

        return Inertia::render('staff/content/blog-categories/Create');
    }

    public function store(StoreBlogCategoryRequest $request): RedirectResponse
    {
        Gate::authorize('manage-content');

        $validated = $request->validated();
        $category = BlogCategory::query()->create([
            ...$validated,
            'slug' => $this->uniqueSlug($validated['name'], BlogCategory::class),
        ]);

        return redirect()->route('staff.content.blog-categories.show', $category);
    }

    public function show(BlogCategory $blogCategory): Response
    {
        Gate::authorize('manage-content');

        return Inertia::render('staff/content/blog-categories/Show', ['category' => $blogCategory]);
    }

    public function edit(BlogCategory $blogCategory): Response
    {
        Gate::authorize('manage-content');

        return Inertia::render('staff/content/blog-categories/Edit', ['category' => $blogCategory]);
    }

    public function update(UpdateBlogCategoryRequest $request, BlogCategory $blogCategory): RedirectResponse
    {
        Gate::authorize('manage-content');

        $validated = $request->validated();
        if (isset($validated['name']) && $validated['name'] !== $blogCategory->name) {
            $validated['slug'] = $this->uniqueSlug($validated['name'], BlogCategory::class, $blogCategory->id);
        }

        $blogCategory->update($validated);

        return redirect()->route('staff.content.blog-categories.show', $blogCategory);
    }

    public function destroy(BlogCategory $blogCategory): RedirectResponse
    {
        Gate::authorize('manage-content');

        $blogCategory->delete();

        return redirect()->route('staff.content.blog-categories.index');
    }
}
