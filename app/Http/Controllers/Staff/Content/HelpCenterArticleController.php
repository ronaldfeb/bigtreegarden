<?php

namespace App\Http\Controllers\Staff\Content;

use App\Http\Controllers\Staff\Controller;
use App\Http\Requests\Staff\Content\StoreHelpCenterArticleRequest;
use App\Http\Requests\Staff\Content\UpdateHelpCenterArticleRequest;
use App\Models\HelpCenterArticle;
use App\Models\HelpCenterCategory;
use App\Models\HelpCenterTopic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class HelpCenterArticleController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('manage-content');

        return Inertia::render('staff/content/help-center-articles/Index', [
            'articles' => HelpCenterArticle::query()
                ->with(['topic', 'author.user'])
                ->latest()
                ->paginate(20),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('manage-content');

        return Inertia::render('staff/content/help-center-articles/Create', [
            'topics' => HelpCenterTopic::query()->orderBy('name')->get(),
            'categories' => HelpCenterCategory::query()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreHelpCenterArticleRequest $request): RedirectResponse
    {
        Gate::authorize('manage-content');

        $validated = $request->validated();
        $categoryIds = $validated['category_ids'] ?? [];
        unset($validated['category_ids']);

        $article = HelpCenterArticle::query()->create([
            ...$validated,
            'slug' => $this->uniqueSlug($validated['title'], HelpCenterArticle::class),
            'author_staff_user_id' => $this->staffUser($request)->id,
        ]);

        if ($categoryIds !== []) {
            $article->categories()->sync($categoryIds);
        }

        return redirect()->route('staff.content.help-center-articles.show', $article);
    }

    public function show(HelpCenterArticle $helpCenterArticle): Response
    {
        Gate::authorize('manage-content');

        $helpCenterArticle->load(['topic', 'author.user', 'categories']);

        return Inertia::render('staff/content/help-center-articles/Show', ['article' => $helpCenterArticle]);
    }

    public function edit(HelpCenterArticle $helpCenterArticle): Response
    {
        Gate::authorize('manage-content');

        $helpCenterArticle->load('categories');

        return Inertia::render('staff/content/help-center-articles/Edit', [
            'article' => $helpCenterArticle,
            'topics' => HelpCenterTopic::query()->orderBy('name')->get(),
            'categories' => HelpCenterCategory::query()->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateHelpCenterArticleRequest $request, HelpCenterArticle $helpCenterArticle): RedirectResponse
    {
        Gate::authorize('manage-content');

        $validated = $request->validated();
        $categoryIds = $validated['category_ids'] ?? null;
        unset($validated['category_ids']);

        if (isset($validated['title']) && $validated['title'] !== $helpCenterArticle->title) {
            $validated['slug'] = $this->uniqueSlug($validated['title'], HelpCenterArticle::class, $helpCenterArticle->id);
        }

        $helpCenterArticle->update($validated);

        if ($categoryIds !== null) {
            $helpCenterArticle->categories()->sync($categoryIds);
        }

        return redirect()->route('staff.content.help-center-articles.show', $helpCenterArticle);
    }

    public function destroy(HelpCenterArticle $helpCenterArticle): RedirectResponse
    {
        Gate::authorize('manage-content');

        $helpCenterArticle->delete();

        return redirect()->route('staff.content.help-center-articles.index');
    }
}
