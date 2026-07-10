<?php

namespace App\Http\Controllers\Staff\Marketing;

use App\Http\Controllers\Staff\Controller;
use App\Http\Requests\Staff\Marketing\StoreMarketingLeadNoteRequest;
use App\Models\MarketingLead;
use App\Models\MarketingLeadNote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class MarketingLeadNoteController extends Controller
{
    public function store(StoreMarketingLeadNoteRequest $request, MarketingLead $marketingLead): RedirectResponse
    {
        Gate::authorize('manage-marketing');

        $marketingLead->notes()->create([
            'body' => $request->validated('body'),
            'staff_user_id' => $this->staffUser($request)->id,
        ]);

        return redirect()->route('staff.marketing.leads.show', $marketingLead);
    }

    public function destroy(MarketingLead $marketingLead, MarketingLeadNote $marketingLeadNote): RedirectResponse
    {
        Gate::authorize('manage-marketing');

        abort_unless($marketingLeadNote->marketing_lead_id === $marketingLead->id, 404);

        $marketingLeadNote->delete();

        return redirect()->route('staff.marketing.leads.show', $marketingLead);
    }
}
