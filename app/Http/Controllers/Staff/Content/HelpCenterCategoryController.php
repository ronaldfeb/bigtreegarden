<?php

namespace App\Http\Controllers\Staff\Content;

use App\Http\Controllers\Staff\Controller;
use App\Http\Requests\Staff\Content\StoreHelpCenterCategoryRequest;
use App\Http\Requests\Staff\Content\UpdateHelpCenterCategoryRequest;
use App\Models\HelpCenterCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class HelpCenterCategoryController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('manage-content');

        return Inertia::render('staff/content/help-center-categories/Index', [
            'categories' => HelpCenterCategory::query()->orderBy('sort_order')->paginate(20),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('manage-content');

        return Inertia::render('staff/content/help-center-categories/Create');
    }

    public function store(StoreHelpCenterCategoryRequest $request): RedirectResponse
    {
        Gate::authorize('manage-content');

        $validated = $request->validated();
        $category = HelpCenterCategory::query()->create([
            ...$validated,
            'slug' => $this->uniqueSlug($validated['name'], HelpCenterCategory::class),
        ]);

        return redirect()->route('staff.content.help-center-categories.show', $category);
    }

    public function show(HelpCenterCategory $helpCenterCategory): Response
    {
        Gate::authorize('manage-content');

        return Inertia::render('staff/content/help-center-categories/Show', ['category' => $helpCenterCategory]);
    }

    public function edit(HelpCenterCategory $helpCenterCategory): Response
    {
        Gate::authorize('manage-content');

        return Inertia::render('staff/content/help-center-categories/Edit', ['category' => $helpCenterCategory]);
    }

    public function update(UpdateHelpCenterCategoryRequest $request, HelpCenterCategory $helpCenterCategory): RedirectResponse
    {
        Gate::authorize('manage-content');

        $validated = $request->validated();
        if (isset($validated['name']) && $validated['name'] !== $helpCenterCategory->name) {
            $validated['slug'] = $this->uniqueSlug($validated['name'], HelpCenterCategory::class, $helpCenterCategory->id);
        }

        $helpCenterCategory->update($validated);

        return redirect()->route('staff.content.help-center-categories.show', $helpCenterCategory);
    }

    public function destroy(HelpCenterCategory $helpCenterCategory): RedirectResponse
    {
        Gate::authorize('manage-content');

        $helpCenterCategory->delete();

        return redirect()->route('staff.content.help-center-categories.index');
    }
}
