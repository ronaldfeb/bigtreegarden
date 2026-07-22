<?php

namespace App\Http\Controllers\Staff\Crm;

use App\Enums\CrmFollowUpStatus;
use App\Enums\CrmFollowUpType;
use App\Enums\CrmInteractionType;
use App\Http\Controllers\Staff\Controller;
use App\Http\Controllers\Staff\Crm\Concerns\ResolvesCrmSubject;
use App\Http\Requests\Staff\Crm\StoreCrmFollowUpRequest;
use App\Http\Requests\Staff\Crm\UpdateCrmFollowUpRequest;
use App\Models\CrmContact;
use App\Models\CrmFollowUp;
use App\Models\CrmOrganisation;
use App\Models\StaffUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class CrmFollowUpController extends Controller
{
    use ResolvesCrmSubject;

    public function index(Request $request): Response
    {
        Gate::authorize('manage-crm');

        $status = $request->string('status')->toString() ?: 'pending';
        $type = $request->string('type')->toString();
        $assignee = $request->string('assigned_staff_user_id')->toString();

        $followUps = CrmFollowUp::query()
            ->with(['assignedStaffUser.user', 'subject'])
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
            ->when($type !== '', fn ($query) => $query->where('type', $type))
            ->when($assignee !== '', fn ($query) => $query->where('assigned_staff_user_id', $assignee))
            ->orderBy('due_at')
            ->paginate(20)
            ->through(fn (CrmFollowUp $followUp): array => $this->transform($followUp))
            ->withQueryString();

        return Inertia::render('staff/crm/follow-ups/Index', [
            'followUps' => $followUps,
            'filters' => [
                'status' => $status,
                'type' => $type,
                'assigned_staff_user_id' => $assignee,
            ],
            'types' => CrmFollowUpType::options(),
            'statuses' => CrmFollowUpStatus::options(),
            'staffUsers' => $this->staffUserOptions(),
        ]);
    }

    public function store(StoreCrmFollowUpRequest $request): RedirectResponse
    {
        Gate::authorize('manage-crm');

        $subject = $this->resolveCrmSubject(
            $request->validated('subject_type'),
            $request->validated('subject_id'),
        );

        $subject->followUps()->create([
            'assigned_staff_user_id' => $request->validated('assigned_staff_user_id') ?? $this->staffUser($request)->id,
            'type' => $request->validated('type'),
            'status' => CrmFollowUpStatus::Pending,
            'title' => $request->validated('title'),
            'notes' => $request->validated('notes'),
            'due_at' => $request->validated('due_at'),
        ]);

        return redirect()->to($this->crmSubjectRedirect($subject));
    }

    public function update(UpdateCrmFollowUpRequest $request, CrmFollowUp $followUp): RedirectResponse
    {
        Gate::authorize('manage-crm');

        $followUp->update($request->validated());

        return redirect()->to($this->crmSubjectRedirect($followUp->subject));
    }

    public function complete(Request $request, CrmFollowUp $followUp): RedirectResponse
    {
        Gate::authorize('manage-crm');

        $followUp->update([
            'status' => CrmFollowUpStatus::Done,
            'completed_at' => now(),
        ]);

        $subject = $followUp->subject;

        $subject->interactions()->create([
            'staff_user_id' => $this->staffUser($request)->id,
            'type' => CrmInteractionType::Note,
            'summary' => 'Completed follow-up: '.$followUp->title,
            'occurred_at' => now(),
        ]);

        $subject->forceFill(['last_contacted_at' => now()])->save();

        return $this->redirectBack($request, $subject);
    }

    public function cancel(Request $request, CrmFollowUp $followUp): RedirectResponse
    {
        Gate::authorize('manage-crm');

        $followUp->update(['status' => CrmFollowUpStatus::Cancelled]);

        return $this->redirectBack($request, $followUp->subject);
    }

    private function redirectBack(Request $request, CrmContact|CrmOrganisation $subject): RedirectResponse
    {
        if ($request->string('redirect')->toString() === 'index') {
            return redirect()->route('staff.crm.follow-ups.index');
        }

        return redirect()->to($this->crmSubjectRedirect($subject));
    }

    /**
     * @return array<string, mixed>
     */
    private function transform(CrmFollowUp $followUp): array
    {
        $subject = $followUp->subject;

        return [
            'id' => $followUp->id,
            'title' => $followUp->title,
            'type' => $followUp->type->value,
            'type_label' => $followUp->type->label(),
            'status' => $followUp->status->value,
            'due_at' => $followUp->due_at,
            'is_overdue' => $followUp->status === CrmFollowUpStatus::Pending && $followUp->due_at->isPast(),
            'assignee' => $followUp->assignedStaffUser?->user?->name,
            'subject_label' => $subject?->name,
            'subject_href' => $subject ? $this->crmSubjectRedirect($subject) : null,
        ];
    }

    /**
     * @return Collection<int, array{id: string, name: string}>
     */
    private function staffUserOptions(): Collection
    {
        return StaffUser::query()
            ->with('user')
            ->where('is_active', true)
            ->get()
            ->map(fn (StaffUser $staffUser): array => [
                'id' => $staffUser->id,
                'name' => $staffUser->user?->name ?? 'Unknown',
            ])
            ->values();
    }
}
