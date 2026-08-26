<?php

namespace App\Http\Controllers;

use App\Http\Requests\AcceptProviderInvitationRequest;
use App\Models\ServiceProviderInvitation;
use App\Models\ServiceProviderUser;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ProviderInvitationController extends Controller
{
    public function show(Request $request, string $token): Response|RedirectResponse
    {
        $invitation = $this->findInvitation($token);

        if ($invitation->isAccepted()) {
            return redirect()->route('login')
                ->with('status', 'This invitation has already been accepted.');
        }

        if ($invitation->isExpired()) {
            return redirect()->route('login')
                ->with('status', 'This invitation has expired.');
        }

        return Inertia::render('auth/AcceptProviderInvitation', [
            'email' => $invitation->email,
            'providerName' => $invitation->serviceProvider?->name,
            'token' => $token,
        ]);
    }

    public function store(AcceptProviderInvitationRequest $request, string $token): RedirectResponse
    {
        $invitation = $this->findInvitation($token);

        if ($invitation->isAccepted() || $invitation->isExpired()) {
            throw ValidationException::withMessages([
                'token' => 'This invitation is no longer valid.',
            ]);
        }

        if (User::query()->whereRaw('lower(email) = ?', [strtolower($invitation->email)])->exists()) {
            throw ValidationException::withMessages([
                'email' => 'An account with this email already exists. Please log in instead.',
            ]);
        }

        $user = DB::transaction(function () use ($request, $invitation): User {
            $user = User::query()->create([
                'name' => $request->validated('name'),
                'email' => $invitation->email,
                'password' => $request->validated('password'),
            ]);

            ServiceProviderUser::query()->create([
                'service_provider_id' => $invitation->service_provider_id,
                'user_id' => $user->id,
                'role' => $invitation->role,
            ]);

            $invitation->update(['accepted_at' => now()]);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('provider.dashboard')
            ->with('status', 'Welcome to the provider portal.');
    }

    private function findInvitation(string $token): ServiceProviderInvitation
    {
        return ServiceProviderInvitation::query()
            ->with('serviceProvider')
            ->where('token_hash', hash('sha256', $token))
            ->firstOrFail();
    }
}
