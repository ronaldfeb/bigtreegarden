<?php

namespace App\Http\Controllers\Staff\Content;

use App\Http\Controllers\Staff\Controller;
use App\Http\Requests\Staff\Content\StorePartnerRequest;
use App\Http\Requests\Staff\Content\UpdatePartnerRequest;
use App\Models\Partner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class PartnerController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('manage-content');

        return Inertia::render('staff/content/partners/Index', [
            'partners' => Partner::query()->orderBy('sort_order')->paginate(20),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('manage-content');

        return Inertia::render('staff/content/partners/Create');
    }

    public function store(StorePartnerRequest $request): RedirectResponse
    {
        Gate::authorize('manage-content');

        $partner = Partner::query()->create($request->validated());

        return redirect()->route('staff.content.partners.show', $partner);
    }

    public function show(Partner $partner): Response
    {
        Gate::authorize('manage-content');

        return Inertia::render('staff/content/partners/Show', ['partner' => $partner]);
    }

    public function edit(Partner $partner): Response
    {
        Gate::authorize('manage-content');

        return Inertia::render('staff/content/partners/Edit', ['partner' => $partner]);
    }

    public function update(UpdatePartnerRequest $request, Partner $partner): RedirectResponse
    {
        Gate::authorize('manage-content');

        $partner->update($request->validated());

        return redirect()->route('staff.content.partners.show', $partner);
    }

    public function destroy(Partner $partner): RedirectResponse
    {
        Gate::authorize('manage-content');

        $partner->delete();

        return redirect()->route('staff.content.partners.index');
    }
}
