<?php

namespace App\Http\Controllers\Staff\Marketing;

use App\Http\Controllers\Staff\Controller;
use App\Http\Requests\Staff\Marketing\StoreMarketingLeadRequest;
use App\Http\Requests\Staff\Marketing\UpdateMarketingLeadRequest;
use App\Models\MarketingAdvert;
use App\Models\MarketingLead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class MarketingLeadController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('manage-marketing');

        return Inertia::render('staff/marketing/leads/Index', [
            'leads' => MarketingLead::query()
                ->with(['staffUser.user', 'marketingAdvert'])
                ->latest()
                ->paginate(20),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('manage-marketing');

        return Inertia::render('staff/marketing/leads/Create', [
            'adverts' => MarketingAdvert::query()->orderBy('client_name')->get(['id', 'client_name', 'code']),
        ]);
    }

    public function store(StoreMarketingLeadRequest $request): RedirectResponse
    {
        Gate::authorize('manage-marketing');

        $lead = MarketingLead::query()->create($request->validated());

        return redirect()->route('staff.marketing.leads.show', $lead);
    }

    public function show(MarketingLead $lead): Response
    {
        Gate::authorize('manage-marketing');

        $lead->load(['staffUser.user', 'marketingAdvert', 'notes.staffUser.user']);

        return Inertia::render('staff/marketing/leads/Show', [
            'lead' => $lead,
        ]);
    }

    public function edit(MarketingLead $lead): Response
    {
        Gate::authorize('manage-marketing');

        return Inertia::render('staff/marketing/leads/Edit', [
            'lead' => $lead,
            'adverts' => MarketingAdvert::query()->orderBy('client_name')->get(['id', 'client_name', 'code']),
        ]);
    }

    public function update(UpdateMarketingLeadRequest $request, MarketingLead $lead): RedirectResponse
    {
        Gate::authorize('manage-marketing');

        $lead->update($request->validated());

        return redirect()->route('staff.marketing.leads.show', $lead);
    }

    public function destroy(MarketingLead $lead): RedirectResponse
    {
        Gate::authorize('manage-marketing');

        $lead->delete();

        return redirect()->route('staff.marketing.leads.index');
    }
}
