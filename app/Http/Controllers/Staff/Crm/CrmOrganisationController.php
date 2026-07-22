<?php

namespace App\Http\Controllers\Staff\Crm;

use App\Enums\CrmFollowUpType;
use App\Enums\CrmInteractionType;
use App\Enums\CrmOrganisationType;
use App\Enums\CrmPartnerStage;
use App\Enums\CrmRelationshipKind;
use App\Enums\CrmRelationshipStatus;
use App\Http\Controllers\Staff\Controller;
use App\Http\Requests\Staff\Crm\StoreCrmOrganisationRequest;
use App\Http\Requests\Staff\Crm\UpdateCrmOrganisationRequest;
use App\Models\CrmOrganisation;
use App\Models\ServiceProvider;
use App\Models\StaffUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class CrmOrganisationController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('manage-crm');

        return Inertia::render('staff/crm/organisations/Index', [
            'organisations' => CrmOrganisation::query()
                ->with('relationshipOwner.user')
                ->withCount(['contacts', 'followUps'])
                ->latest()
                ->paginate(20),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('manage-crm');

        return Inertia::render('staff/crm/organisations/Create', $this->formOptions());
    }

    public function store(StoreCrmOrganisationRequest $request): RedirectResponse
    {
        Gate::authorize('manage-crm');

        $organisation = CrmOrganisation::query()->create($request->validated());

        return redirect()->route('staff.crm.organisations.show', $organisation);
    }

    public function show(CrmOrganisation $organisation): Response
    {
        Gate::authorize('manage-crm');

        $organisation->load([
            'relationshipOwner.user',
            'serviceProvider',
            'contacts.relationshipOwner.user',
            'interactions' => fn ($query) => $query->with('staffUser.user')->latest('occurred_at'),
            'followUps' => fn ($query) => $query->with('assignedStaffUser.user')->orderBy('due_at'),
        ]);

        return Inertia::render('staff/crm/organisations/Show', [
            'organisation' => $organisation,
            'partnerStages' => CrmPartnerStage::options(),
            'interactionTypes' => CrmInteractionType::options(),
            'followUpTypes' => CrmFollowUpType::options(),
            'staffUsers' => $this->staffUserOptions(),
        ]);
    }

    public function edit(CrmOrganisation $organisation): Response
    {
        Gate::authorize('manage-crm');

        return Inertia::render('staff/crm/organisations/Edit', [
            'organisation' => $organisation,
        ] + $this->formOptions());
    }

    public function update(UpdateCrmOrganisationRequest $request, CrmOrganisation $organisation): RedirectResponse
    {
        Gate::authorize('manage-crm');

        $organisation->update($request->validated());

        return redirect()->route('staff.crm.organisations.show', $organisation);
    }

    public function destroy(CrmOrganisation $organisation): RedirectResponse
    {
        Gate::authorize('manage-crm');

        $organisation->delete();

        return redirect()->route('staff.crm.organisations.index');
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        return [
            'types' => CrmOrganisationType::options(),
            'relationshipKinds' => CrmRelationshipKind::options(),
            'partnerStages' => CrmPartnerStage::options(),
            'relationshipStatuses' => CrmRelationshipStatus::options(),
            'staffUsers' => $this->staffUserOptions(),
            'serviceProviders' => ServiceProvider::query()
                ->orderBy('name')
                ->get(['id', 'name']),
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
