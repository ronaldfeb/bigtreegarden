<?php

namespace App\Http\Controllers\Staff\Directory;

use App\Http\Controllers\Staff\Controller;
use App\Http\Requests\Staff\Directory\StoreServiceProviderRequest;
use App\Http\Requests\Staff\Directory\UpdateServiceProviderRequest;
use App\Models\ServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ServiceProviderController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('manage-directory');

        return Inertia::render('staff/directory/service-providers/Index', [
            'serviceProviders' => ServiceProvider::query()->latest()->paginate(20),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('manage-directory');

        return Inertia::render('staff/directory/service-providers/Create');
    }

    public function store(StoreServiceProviderRequest $request): RedirectResponse
    {
        Gate::authorize('manage-directory');

        $validated = $request->validated();
        $serviceProvider = ServiceProvider::query()->create([
            ...$validated,
            'slug' => $this->uniqueSlug($validated['name'], ServiceProvider::class),
        ]);

        return redirect()->route('staff.directory.service-providers.show', $serviceProvider);
    }

    public function show(ServiceProvider $serviceProvider): Response
    {
        Gate::authorize('manage-directory');

        $serviceProvider->load([
            'memberships.user:id,name,email',
            'creditPurchases' => fn ($query) => $query->latest()->limit(20),
            'personsOfInterest' => fn ($query) => $query->latest()->limit(20),
        ]);

        return Inertia::render('staff/directory/service-providers/Show', [
            'serviceProvider' => [
                'id' => $serviceProvider->id,
                'name' => $serviceProvider->name,
                'slug' => $serviceProvider->slug,
                'status' => $serviceProvider->status,
                'registration_number' => $serviceProvider->registration_number,
                'vat_number' => $serviceProvider->vat_number,
                'email' => $serviceProvider->email,
                'phone' => $serviceProvider->phone,
                'city' => $serviceProvider->city,
                'province' => $serviceProvider->province,
                'credits_remaining' => $serviceProvider->credits_remaining,
                'description' => $serviceProvider->description,
                'physical_address' => $serviceProvider->physical_address,
                'website_url' => $serviceProvider->website_url,
                'created_at' => $serviceProvider->created_at?->toIso8601String(),
            ],
            'members' => $serviceProvider->memberships->map(fn ($membership): array => [
                'user_id' => $membership->user_id,
                'name' => $membership->user?->name,
                'email' => $membership->user?->email,
                'role' => $membership->role instanceof \BackedEnum
                    ? $membership->role->value
                    : $membership->role,
            ])->values()->all(),
            'purchases' => $serviceProvider->creditPurchases->map(fn ($purchase): array => [
                'id' => $purchase->id,
                'package_name' => $purchase->package_name,
                'page_count' => $purchase->page_count,
                'price_cents' => $purchase->price_cents,
                'payment_method' => $purchase->payment_method instanceof \BackedEnum
                    ? $purchase->payment_method->value
                    : $purchase->payment_method,
                'status' => $purchase->status instanceof \BackedEnum
                    ? $purchase->status->value
                    : $purchase->status,
                'payment_reference' => $purchase->payment_reference,
            ])->values()->all(),
            'memorials' => $serviceProvider->personsOfInterest->map(fn ($poi): array => [
                'id' => $poi->id,
                'display_name' => $poi->display_name,
                'public_slug' => $poi->public_slug,
                'status' => $poi->status instanceof \BackedEnum
                    ? $poi->status->value
                    : $poi->status,
            ])->values()->all(),
        ]);
    }

    public function edit(ServiceProvider $serviceProvider): Response
    {
        Gate::authorize('manage-directory');

        return Inertia::render('staff/directory/service-providers/Edit', ['serviceProvider' => $serviceProvider]);
    }

    public function update(UpdateServiceProviderRequest $request, ServiceProvider $serviceProvider): RedirectResponse
    {
        Gate::authorize('manage-directory');

        $validated = $request->validated();
        if (isset($validated['name']) && $validated['name'] !== $serviceProvider->name) {
            $validated['slug'] = $this->uniqueSlug($validated['name'], ServiceProvider::class, $serviceProvider->id);
        }

        $serviceProvider->update($validated);

        return redirect()->route('staff.directory.service-providers.show', $serviceProvider);
    }

    public function destroy(ServiceProvider $serviceProvider): RedirectResponse
    {
        Gate::authorize('manage-directory');

        $serviceProvider->delete();

        return redirect()->route('staff.directory.service-providers.index');
    }

    public function approve(ServiceProvider $serviceProvider): RedirectResponse
    {
        Gate::authorize('manage-directory');

        abort_unless(
            $serviceProvider->status === config('constants.service_provider.status.pending'),
            422,
            'Only pending service providers can be approved.',
        );

        $serviceProvider->update(['status' => config('constants.service_provider.status.active')]);

        return back()->with('status', 'Service provider approved.');
    }

    public function suspend(ServiceProvider $serviceProvider): RedirectResponse
    {
        Gate::authorize('manage-directory');

        $serviceProvider->update(['status' => config('constants.service_provider.status.suspended')]);

        return back()->with('status', 'Service provider suspended.');
    }
}
