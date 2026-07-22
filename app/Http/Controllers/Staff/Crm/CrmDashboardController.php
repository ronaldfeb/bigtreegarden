<?php

namespace App\Http\Controllers\Staff\Crm;

use App\Enums\CrmLifecycleStage;
use App\Enums\CrmPartnerStage;
use App\Enums\CrmRelationshipStatus;
use App\Http\Controllers\Staff\Controller;
use App\Models\CrmContact;
use App\Models\CrmFollowUp;
use App\Models\CrmOrganisation;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class CrmDashboardController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('manage-crm');

        $staffUser = $this->staffUser($request);

        $dueFollowUps = CrmFollowUp::query()
            ->due()
            ->where('assigned_staff_user_id', $staffUser->id)
            ->with('subject')
            ->orderBy('due_at')
            ->limit(10)
            ->get()
            ->map(fn (CrmFollowUp $followUp): array => [
                'id' => $followUp->id,
                'title' => $followUp->title,
                'type_label' => $followUp->type->label(),
                'due_at' => $followUp->due_at,
                'subject_label' => $followUp->subject?->name,
                'subject_href' => $followUp->subject
                    ? ($followUp->subject instanceof CrmContact
                        ? route('staff.crm.contacts.show', $followUp->subject)
                        : route('staff.crm.organisations.show', $followUp->subject))
                    : null,
            ]);

        return Inertia::render('staff/crm/Dashboard', [
            'counts' => [
                'organisations' => CrmOrganisation::query()->count(),
                'contacts' => CrmContact::query()->count(),
                'due_follow_ups' => CrmFollowUp::query()->due()->count(),
                'my_due_follow_ups' => $dueFollowUps->count(),
            ],
            'relationshipStatuses' => $this->pipeline(
                CrmOrganisation::query()
                    ->selectRaw('relationship_status, count(*) as total')
                    ->groupBy('relationship_status')
                    ->pluck('total', 'relationship_status'),
                CrmRelationshipStatus::cases(),
            ),
            'partnerStages' => $this->pipeline(
                CrmOrganisation::query()
                    ->whereNotNull('partner_stage')
                    ->selectRaw('partner_stage, count(*) as total')
                    ->groupBy('partner_stage')
                    ->pluck('total', 'partner_stage'),
                CrmPartnerStage::cases(),
            ),
            'lifecycleStages' => $this->pipeline(
                CrmContact::query()
                    ->selectRaw('lifecycle_stage, count(*) as total')
                    ->groupBy('lifecycle_stage')
                    ->pluck('total', 'lifecycle_stage'),
                CrmLifecycleStage::cases(),
            ),
            'dueFollowUps' => $dueFollowUps,
        ]);
    }

    /**
     * @param  Collection<string, int>  $counts
     * @param  array<int, \BackedEnum>  $cases
     * @return array<int, array{value: string, label: string, total: int}>
     */
    private function pipeline($counts, array $cases): array
    {
        return array_map(fn ($case): array => [
            'value' => $case->value,
            'label' => $case->label(),
            'total' => (int) ($counts[$case->value] ?? 0),
        ], $cases);
    }
}
