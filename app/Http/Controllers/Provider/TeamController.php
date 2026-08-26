<?php

namespace App\Http\Controllers\Provider;

use App\Enums\ServiceProviderRole;
use App\Http\Requests\Provider\InviteTeamMemberRequest;
use App\Http\Requests\Provider\UpdateTeamMemberRequest;
use App\Mail\ServiceProviderTeamAddedMail;
use App\Mail\ServiceProviderTeamInvitationMail;
use App\Models\ServiceProviderInvitation;
use App\Models\ServiceProviderUser;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class TeamController extends Controller
{
    public function index(Request $request): Response
    {
        $serviceProvider = $this->currentProvider($request);

        $members = ServiceProviderUser::query()
            ->with('user:id,name,email')
            ->where('service_provider_id', $serviceProvider->id)
            ->get()
            ->map(fn (ServiceProviderUser $membership): array => [
                'user_id' => $membership->user_id,
                'name' => $membership->user?->name,
                'email' => $membership->user?->email,
                'role' => $membership->role->value,
            ]);

        $invitations = ServiceProviderInvitation::query()
            ->where('service_provider_id', $serviceProvider->id)
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->get()
            ->map(fn (ServiceProviderInvitation $invitation): array => [
                'id' => $invitation->id,
                'email' => $invitation->email,
                'role' => $invitation->role->value,
                'expires_at' => $invitation->expires_at?->toIso8601String(),
            ]);

        return Inertia::render('provider/Team', [
            'members' => $members,
            'invitations' => $invitations,
            'roles' => collect(ServiceProviderRole::cases())->map(fn (ServiceProviderRole $role): array => [
                'value' => $role->value,
                'label' => ucfirst($role->value),
            ])->values()->all(),
        ]);
    }

    public function store(InviteTeamMemberRequest $request): RedirectResponse
    {
        $serviceProvider = $this->currentProvider($request);
        $email = Str::lower($request->validated('email'));
        $role = ServiceProviderRole::from($request->validated('role'));

        $existingUser = User::query()->whereRaw('lower(email) = ?', [$email])->first();

        if ($existingUser !== null) {
            if ($existingUser->serviceProviderMembership !== null) {
                throw ValidationException::withMessages([
                    'email' => 'That user already belongs to a service provider.',
                ]);
            }

            ServiceProviderUser::query()->create([
                'service_provider_id' => $serviceProvider->id,
                'user_id' => $existingUser->id,
                'role' => $role,
            ]);

            Mail::to($existingUser)->send(new ServiceProviderTeamAddedMail(
                $serviceProvider,
                $existingUser,
                $role,
            ));

            return back()->with('status', 'Team member added.');
        }

        $pendingExists = ServiceProviderInvitation::query()
            ->where('service_provider_id', $serviceProvider->id)
            ->whereRaw('lower(email) = ?', [$email])
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->exists();

        if ($pendingExists) {
            throw ValidationException::withMessages([
                'email' => 'An invitation has already been sent to that email.',
            ]);
        }

        $plainToken = Str::random(64);

        $invitation = ServiceProviderInvitation::query()->create([
            'service_provider_id' => $serviceProvider->id,
            'invited_by_user_id' => $request->user()->id,
            'email' => $email,
            'role' => $role,
            'token_hash' => hash('sha256', $plainToken),
            'expires_at' => now()->addDays(7),
        ]);

        Mail::to($email)->send(new ServiceProviderTeamInvitationMail(
            $invitation,
            $serviceProvider,
            $plainToken,
        ));

        return back()->with('status', 'Invitation sent.');
    }

    public function update(UpdateTeamMemberRequest $request, string $userId): RedirectResponse
    {
        $serviceProvider = $this->currentProvider($request);
        $role = ServiceProviderRole::from($request->validated('role'));

        $membership = ServiceProviderUser::query()
            ->where('service_provider_id', $serviceProvider->id)
            ->where('user_id', $userId)
            ->firstOrFail();

        if (
            $membership->isOwner()
            && $role !== ServiceProviderRole::Owner
            && $this->ownerCount($serviceProvider->id) <= 1
        ) {
            throw ValidationException::withMessages([
                'role' => 'You cannot demote the last owner.',
            ]);
        }

        $membership->update(['role' => $role]);

        return back()->with('status', 'Team member updated.');
    }

    public function destroy(Request $request, string $userId): RedirectResponse
    {
        $serviceProvider = $this->currentProvider($request);

        $membership = ServiceProviderUser::query()
            ->where('service_provider_id', $serviceProvider->id)
            ->where('user_id', $userId)
            ->firstOrFail();

        if ($membership->isOwner() && $this->ownerCount($serviceProvider->id) <= 1) {
            throw ValidationException::withMessages([
                'user' => 'You cannot remove the last owner.',
            ]);
        }

        $membership->delete();

        return back()->with('status', 'Team member removed.');
    }

    public function destroyInvitation(Request $request, ServiceProviderInvitation $invitation): RedirectResponse
    {
        $serviceProvider = $this->currentProvider($request);

        abort_unless($invitation->service_provider_id === $serviceProvider->id, 404);

        $invitation->delete();

        return back()->with('status', 'Invitation cancelled.');
    }

    private function ownerCount(string $serviceProviderId): int
    {
        return ServiceProviderUser::query()
            ->where('service_provider_id', $serviceProviderId)
            ->where('role', ServiceProviderRole::Owner)
            ->count();
    }
}
