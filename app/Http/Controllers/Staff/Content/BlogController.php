<?php

namespace App\Http\Controllers\Staff\Content;

use App\Http\Controllers\Staff\Controller;
use App\Http\Requests\Staff\Content\StoreBlogRequest;
use App\Http\Requests\Staff\Content\UpdateBlogRequest;
use App\Models\Blog;
use App\Models\BlogCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class BlogController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('manage-content');

        return Inertia::render('staff/content/blogs/Index', [
            'blogs' => Blog::query()->with(['blogCategory', 'author.user'])->latest()->paginate(20),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('manage-content');

        return Inertia::render('staff/content/blogs/Create', [
            'categories' => BlogCategory::query()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreBlogRequest $request): RedirectResponse
    {
        Gate::authorize('manage-content');

        $validated = $request->validated();
        $blog = Blog::query()->create([
            ...$validated,
            'slug' => $this->uniqueSlug($validated['title'], Blog::class),
            'author_staff_user_id' => $this->staffUser($request)->id,
        ]);

        return redirect()->route('staff.content.blogs.show', $blog);
    }

    public function show(Blog $blog): Response
    {
        Gate::authorize('manage-content');

        $blog->load(['blogCategory', 'author.user']);

        return Inertia::render('staff/content/blogs/Show', ['blog' => $blog]);
    }

    public function edit(Blog $blog): Response
    {
        Gate::authorize('manage-content');

        return Inertia::render('staff/content/blogs/Edit', [
            'blog' => $blog,
            'categories' => BlogCategory::query()->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateBlogRequest $request, Blog $blog): RedirectResponse
    {
        Gate::authorize('manage-content');

        $validated = $request->validated();
        if (isset($validated['title']) && $validated['title'] !== $blog->title) {
            $validated['slug'] = $this->uniqueSlug($validated['title'], Blog::class, $blog->id);
        }

        $blog->update($validated);

        return redirect()->route('staff.content.blogs.show', $blog);
    }

    public function destroy(Blog $blog): RedirectResponse
    {
        Gate::authorize('manage-content');

        $blog->delete();

        return redirect()->route('staff.content.blogs.index');
    }
}
