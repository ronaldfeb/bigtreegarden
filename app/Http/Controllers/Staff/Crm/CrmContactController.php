<?php

namespace App\Http\Controllers\Staff\Crm;

use App\Enums\CrmFollowUpType;
use App\Enums\CrmInteractionType;
use App\Enums\CrmJourney;
use App\Enums\CrmLifecycleStage;
use App\Http\Controllers\Staff\Controller;
use App\Http\Requests\Staff\Crm\StoreCrmContactRequest;
use App\Http\Requests\Staff\Crm\UpdateCrmContactRequest;
use App\Models\CrmContact;
use App\Models\CrmOrganisation;
use App\Models\StaffUser;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class CrmContactController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('manage-crm');

        return Inertia::render('staff/crm/contacts/Index', [
            'contacts' => CrmContact::query()
                ->with(['organisation', 'relationshipOwner.user'])
                ->latest()
                ->paginate(20),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('manage-crm');

        return Inertia::render('staff/crm/contacts/Create', $this->formOptions());
    }

    public function store(StoreCrmContactRequest $request): RedirectResponse
    {
        Gate::authorize('manage-crm');

        $contact = CrmContact::query()->create($request->validated());

        return redirect()->route('staff.crm.contacts.show', $contact);
    }

    public function show(CrmContact $contact): Response
    {
        Gate::authorize('manage-crm');

        $contact->load([
            'organisation',
            'relationshipOwner.user',
            'user',
            'marketingLead',
            'interactions' => fn ($query) => $query->with('staffUser.user')->latest('occurred_at'),
            'followUps' => fn ($query) => $query->with('assignedStaffUser.user')->orderBy('due_at'),
        ]);

        return Inertia::render('staff/crm/contacts/Show', [
            'contact' => $contact,
            'linkedActivity' => $this->linkedActivity($contact),
            'interactionTypes' => CrmInteractionType::options(),
            'followUpTypes' => CrmFollowUpType::options(),
            'staffUsers' => $this->staffUserOptions(),
        ]);
    }

    public function edit(CrmContact $contact): Response
    {
        Gate::authorize('manage-crm');

        return Inertia::render('staff/crm/contacts/Edit', [
            'contact' => $contact,
        ] + $this->formOptions());
    }

    public function update(UpdateCrmContactRequest $request, CrmContact $contact): RedirectResponse
    {
        Gate::authorize('manage-crm');

        $contact->update($request->validated());

        return redirect()->route('staff.crm.contacts.show', $contact);
    }

    public function destroy(CrmContact $contact): RedirectResponse
    {
        Gate::authorize('manage-crm');

        $contact->delete();

        return redirect()->route('staff.crm.contacts.index');
    }

    /**
     * @return array<string, mixed>|null
     */
    private function linkedActivity(CrmContact $contact): ?array
    {
        if ($contact->user === null) {
            return null;
        }

        $user = $contact->user->loadCount(['personsOfInterest', 'transactions', 'subscriptions']);

        return [
            'persons_of_interest' => $user->persons_of_interest_count,
            'transactions' => $user->transactions_count,
            'subscriptions' => $user->subscriptions_count,
            'has_active_subscription' => $user->hasActiveSubscription(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        return [
            'journeys' => CrmJourney::options(),
            'lifecycleStages' => CrmLifecycleStage::options(),
            'organisations' => CrmOrganisation::query()->orderBy('name')->get(['id', 'name']),
            'staffUsers' => $this->staffUserOptions(),
            'users' => User::query()->orderBy('name')->get(['id', 'name', 'email']),
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
