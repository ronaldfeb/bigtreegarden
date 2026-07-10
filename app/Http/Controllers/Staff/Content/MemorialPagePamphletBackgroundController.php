<?php

namespace App\Http\Controllers\Staff\Content;

use App\Http\Controllers\Staff\Controller;
use App\Http\Requests\Staff\Content\StoreMemorialPagePamphletBackgroundRequest;
use App\Http\Requests\Staff\Content\UpdateMemorialPagePamphletBackgroundRequest;
use App\Models\MemorialPagePamphletBackground;
use App\Models\MemorialPagePamphletBackgroundCollection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class MemorialPagePamphletBackgroundController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('manage-content');

        return Inertia::render('staff/content/pamphlet-backgrounds/Index', [
            'backgrounds' => MemorialPagePamphletBackground::query()
                ->with('collection')
                ->orderBy('sort_order')
                ->paginate(20),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('manage-content');

        return Inertia::render('staff/content/pamphlet-backgrounds/Create', [
            'collections' => MemorialPagePamphletBackgroundCollection::query()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreMemorialPagePamphletBackgroundRequest $request): RedirectResponse
    {
        Gate::authorize('manage-content');

        $background = MemorialPagePamphletBackground::query()->create($request->validated());

        return redirect()->route('staff.content.pamphlet-backgrounds.show', $background);
    }

    public function show(MemorialPagePamphletBackground $pamphletBackground): Response
    {
        Gate::authorize('manage-content');

        $pamphletBackground->load('collection');

        return Inertia::render('staff/content/pamphlet-backgrounds/Show', ['background' => $pamphletBackground]);
    }

    public function edit(MemorialPagePamphletBackground $pamphletBackground): Response
    {
        Gate::authorize('manage-content');

        return Inertia::render('staff/content/pamphlet-backgrounds/Edit', [
            'background' => $pamphletBackground,
            'collections' => MemorialPagePamphletBackgroundCollection::query()->orderBy('name')->get(),
        ]);
    }

    public function update(
        UpdateMemorialPagePamphletBackgroundRequest $request,
        MemorialPagePamphletBackground $pamphletBackground
    ): RedirectResponse {
        Gate::authorize('manage-content');

        $pamphletBackground->update($request->validated());

        return redirect()->route('staff.content.pamphlet-backgrounds.show', $pamphletBackground);
    }

    public function destroy(MemorialPagePamphletBackground $pamphletBackground): RedirectResponse
    {
        Gate::authorize('manage-content');

        $pamphletBackground->delete();

        return redirect()->route('staff.content.pamphlet-backgrounds.index');
    }
}
