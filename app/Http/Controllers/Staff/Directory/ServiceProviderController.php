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

        return Inertia::render('staff/directory/service-providers/Show', ['serviceProvider' => $serviceProvider]);
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
