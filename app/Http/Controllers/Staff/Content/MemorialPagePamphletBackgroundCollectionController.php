<?php

namespace App\Http\Controllers\Staff\Content;

use App\Http\Controllers\Staff\Controller;
use App\Http\Requests\Staff\Content\StoreMemorialPagePamphletBackgroundCollectionRequest;
use App\Http\Requests\Staff\Content\UpdateMemorialPagePamphletBackgroundCollectionRequest;
use App\Models\MemorialPagePamphletBackgroundCollection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class MemorialPagePamphletBackgroundCollectionController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('manage-content');

        return Inertia::render('staff/content/pamphlet-background-collections/Index', [
            'collections' => MemorialPagePamphletBackgroundCollection::query()
                ->withCount('backgrounds')
                ->orderBy('sort_order')
                ->paginate(20),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('manage-content');

        return Inertia::render('staff/content/pamphlet-background-collections/Create');
    }

    public function store(StoreMemorialPagePamphletBackgroundCollectionRequest $request): RedirectResponse
    {
        Gate::authorize('manage-content');

        $validated = $request->validated();
        $collection = MemorialPagePamphletBackgroundCollection::query()->create([
            ...$validated,
            'slug' => $this->uniqueSlug($validated['name'], MemorialPagePamphletBackgroundCollection::class),
        ]);

        return redirect()->route('staff.content.pamphlet-background-collections.show', $collection);
    }

    public function show(MemorialPagePamphletBackgroundCollection $pamphletBackgroundCollection): Response
    {
        Gate::authorize('manage-content');

        $pamphletBackgroundCollection->load('backgrounds');

        return Inertia::render('staff/content/pamphlet-background-collections/Show', [
            'collection' => $pamphletBackgroundCollection,
        ]);
    }

    public function edit(MemorialPagePamphletBackgroundCollection $pamphletBackgroundCollection): Response
    {
        Gate::authorize('manage-content');

        return Inertia::render('staff/content/pamphlet-background-collections/Edit', [
            'collection' => $pamphletBackgroundCollection,
        ]);
    }

    public function update(
        UpdateMemorialPagePamphletBackgroundCollectionRequest $request,
        MemorialPagePamphletBackgroundCollection $pamphletBackgroundCollection
    ): RedirectResponse {
        Gate::authorize('manage-content');

        $validated = $request->validated();
        if (isset($validated['name']) && $validated['name'] !== $pamphletBackgroundCollection->name) {
            $validated['slug'] = $this->uniqueSlug(
                $validated['name'],
                MemorialPagePamphletBackgroundCollection::class,
                $pamphletBackgroundCollection->id
            );
        }

        $pamphletBackgroundCollection->update($validated);

        return redirect()->route('staff.content.pamphlet-background-collections.show', $pamphletBackgroundCollection);
    }

    public function destroy(MemorialPagePamphletBackgroundCollection $pamphletBackgroundCollection): RedirectResponse
    {
        Gate::authorize('manage-content');

        $pamphletBackgroundCollection->delete();

        return redirect()->route('staff.content.pamphlet-background-collections.index');
    }
}
