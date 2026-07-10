<?php

namespace App\Http\Controllers\Staff\Directory;

use App\Http\Controllers\Staff\Controller;
use App\Http\Requests\Staff\Directory\StorePersonOfInterestRequest;
use App\Http\Requests\Staff\Directory\UpdatePersonOfInterestRequest;
use App\Models\PersonOfInterest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PersonOfInterestController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('manage-directory');

        return Inertia::render('staff/directory/persons-of-interest/Index', [
            'personsOfInterest' => PersonOfInterest::query()
                ->with('createdBy')
                ->latest()
                ->paginate(20),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('manage-directory');

        return Inertia::render('staff/directory/persons-of-interest/Create');
    }

    public function store(StorePersonOfInterestRequest $request): RedirectResponse
    {
        Gate::authorize('manage-directory');

        $validated = $request->validated();
        $displayName = $validated['display_name'] ?? trim($validated['first_name'].' '.$validated['last_name']);

        $person = PersonOfInterest::query()->create([
            ...$validated,
            'display_name' => $displayName,
            'public_slug' => $this->uniquePublicSlug($displayName),
        ]);

        return redirect()->route('staff.directory.persons-of-interest.show', $person);
    }

    public function show(PersonOfInterest $personOfInterest): Response
    {
        Gate::authorize('manage-directory');

        $personOfInterest->load(['createdBy', 'memorialPages']);

        return Inertia::render('staff/directory/persons-of-interest/Show', ['personOfInterest' => $personOfInterest]);
    }

    public function edit(PersonOfInterest $personOfInterest): Response
    {
        Gate::authorize('manage-directory');

        return Inertia::render('staff/directory/persons-of-interest/Edit', ['personOfInterest' => $personOfInterest]);
    }

    public function update(UpdatePersonOfInterestRequest $request, PersonOfInterest $personOfInterest): RedirectResponse
    {
        Gate::authorize('manage-directory');

        $personOfInterest->update($request->validated());

        return redirect()->route('staff.directory.persons-of-interest.show', $personOfInterest);
    }

    public function destroy(PersonOfInterest $personOfInterest): RedirectResponse
    {
        Gate::authorize('manage-directory');

        $personOfInterest->delete();

        return redirect()->route('staff.directory.persons-of-interest.index');
    }

    protected function uniquePublicSlug(string $value): string
    {
        $baseSlug = Str::slug($value);
        $slug = $baseSlug;
        $counter = 1;

        while (PersonOfInterest::query()->where('public_slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
