<?php

namespace App\Http\Controllers\Staff\Content;

use App\Http\Controllers\Staff\Controller;
use App\Http\Requests\Staff\Content\StoreAmbassadorRequest;
use App\Http\Requests\Staff\Content\UpdateAmbassadorRequest;
use App\Models\Ambassador;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class AmbassadorController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('manage-content');

        return Inertia::render('staff/content/ambassadors/Index', [
            'ambassadors' => Ambassador::query()->withCount('images')->orderBy('sort_order')->paginate(20),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('manage-content');

        return Inertia::render('staff/content/ambassadors/Create');
    }

    public function store(StoreAmbassadorRequest $request): RedirectResponse
    {
        Gate::authorize('manage-content');

        $validated = $request->validated();
        $ambassador = Ambassador::query()->create([
            ...$validated,
            'slug' => $this->uniqueSlug($validated['name'], Ambassador::class),
        ]);

        return redirect()->route('staff.content.ambassadors.show', $ambassador);
    }

    public function show(Ambassador $ambassador): Response
    {
        Gate::authorize('manage-content');

        $ambassador->load('images');

        return Inertia::render('staff/content/ambassadors/Show', ['ambassador' => $ambassador]);
    }

    public function edit(Ambassador $ambassador): Response
    {
        Gate::authorize('manage-content');

        return Inertia::render('staff/content/ambassadors/Edit', ['ambassador' => $ambassador]);
    }

    public function update(UpdateAmbassadorRequest $request, Ambassador $ambassador): RedirectResponse
    {
        Gate::authorize('manage-content');

        $validated = $request->validated();
        if (isset($validated['name']) && $validated['name'] !== $ambassador->name) {
            $validated['slug'] = $this->uniqueSlug($validated['name'], Ambassador::class, $ambassador->id);
        }

        $ambassador->update($validated);

        return redirect()->route('staff.content.ambassadors.show', $ambassador);
    }

    public function destroy(Ambassador $ambassador): RedirectResponse
    {
        Gate::authorize('manage-content');

        $ambassador->delete();

        return redirect()->route('staff.content.ambassadors.index');
    }
}
