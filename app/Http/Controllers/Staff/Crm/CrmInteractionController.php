<?php

namespace App\Http\Controllers\Staff\Crm;

use App\Http\Controllers\Staff\Controller;
use App\Http\Controllers\Staff\Crm\Concerns\ResolvesCrmSubject;
use App\Http\Requests\Staff\Crm\StoreCrmInteractionRequest;
use App\Models\CrmInteraction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class CrmInteractionController extends Controller
{
    use ResolvesCrmSubject;

    public function store(StoreCrmInteractionRequest $request): RedirectResponse
    {
        Gate::authorize('manage-crm');

        $subject = $this->resolveCrmSubject(
            $request->validated('subject_type'),
            $request->validated('subject_id'),
        );

        $occurredAt = $request->validated('occurred_at') ?? now();

        $subject->interactions()->create([
            'staff_user_id' => $this->staffUser($request)->id,
            'type' => $request->validated('type'),
            'summary' => $request->validated('summary'),
            'body' => $request->validated('body'),
            'occurred_at' => $occurredAt,
        ]);

        $subject->forceFill(['last_contacted_at' => $occurredAt])->save();

        return redirect()->to($this->crmSubjectRedirect($subject));
    }

    public function destroy(CrmInteraction $interaction): RedirectResponse
    {
        Gate::authorize('manage-crm');

        $subject = $interaction->subject;

        $interaction->delete();

        return redirect()->to($this->crmSubjectRedirect($subject));
    }
}
