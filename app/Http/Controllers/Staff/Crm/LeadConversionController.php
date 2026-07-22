<?php

namespace App\Http\Controllers\Staff\Crm;

use App\Enums\CrmInteractionType;
use App\Enums\CrmLifecycleStage;
use App\Http\Controllers\Staff\Controller;
use App\Models\CrmContact;
use App\Models\MarketingLead;
use App\Models\MarketingLeadNote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class LeadConversionController extends Controller
{
    public function store(MarketingLead $lead): RedirectResponse
    {
        Gate::authorize('manage-crm');

        $existing = CrmContact::query()->where('marketing_lead_id', $lead->id)->first();

        if ($existing !== null) {
            return redirect()->route('staff.crm.contacts.show', $existing);
        }

        $lead->loadMissing('notes.staffUser');

        $contact = CrmContact::query()->create([
            'marketing_lead_id' => $lead->id,
            'relationship_owner_staff_user_id' => $lead->staff_user_id,
            'name' => $lead->name,
            'email' => $lead->email,
            'phone' => $lead->phone,
            'lifecycle_stage' => CrmLifecycleStage::Enquiry,
        ]);

        $lead->notes->each(function (MarketingLeadNote $note) use ($contact): void {
            $contact->interactions()->create([
                'staff_user_id' => $note->staff_user_id,
                'type' => CrmInteractionType::Note,
                'summary' => 'Imported from lead note',
                'body' => $note->body,
                'occurred_at' => $note->created_at,
            ]);
        });

        $lead->update(['status' => 'converted']);

        return redirect()->route('staff.crm.contacts.show', $contact);
    }
}
