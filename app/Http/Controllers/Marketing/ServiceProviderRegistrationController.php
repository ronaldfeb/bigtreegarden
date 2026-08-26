<?php

namespace App\Http\Controllers\Marketing;

use App\Enums\ServiceProviderRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Marketing\RegisterServiceProviderRequest;
use App\Models\ServiceProvider;
use App\Models\ServiceProviderUser;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

class ServiceProviderRegistrationController extends Controller
{
    public function create(Request $request): Response|RedirectResponse
    {
        if ($request->user()?->serviceProviderMembership !== null) {
            return redirect()->route('provider.dashboard');
        }

        return Inertia::render('marketing/Providers/Register', [
            'canRegister' => Features::enabled(Features::registration()),
            'authenticated' => $request->user() !== null,
            'user' => $request->user()?->only(['name', 'email']),
        ]);
    }

    public function store(RegisterServiceProviderRequest $request): RedirectResponse
    {
        if ($request->user()?->serviceProviderMembership !== null) {
            return redirect()->route('provider.dashboard');
        }

        $validated = $request->validated();

        $user = DB::transaction(function () use ($request, $validated): User {
            $user = $request->user();

            if ($user === null) {
                $user = User::query()->create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'phone' => $validated['contact_phone'],
                    'password' => $validated['password'],
                ]);
            }

            $serviceProvider = ServiceProvider::query()->create([
                'name' => $validated['business_name'],
                'slug' => $this->uniqueSlug($validated['business_name']),
                'registration_number' => $validated['registration_number'],
                'vat_number' => $validated['vat_number'] ?? null,
                'email' => $validated['contact_email'],
                'phone' => $validated['contact_phone'],
                'status' => config('constants.service_provider.status.pending'),
                'credits_remaining' => 0,
            ]);

            ServiceProviderUser::query()->create([
                'service_provider_id' => $serviceProvider->id,
                'user_id' => $user->id,
                'role' => ServiceProviderRole::Owner,
            ]);

            return $user;
        });

        if ($request->user() === null) {
            Auth::login($user);
            $request->session()->regenerate();
        }

        return redirect()->route('provider.dashboard')
            ->with('status', 'Your funeral home registration was submitted and is awaiting approval.');
    }

    private function uniqueSlug(string $value): string
    {
        $baseSlug = Str::slug($value);
        $slug = $baseSlug;
        $counter = 1;

        while (ServiceProvider::query()->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
