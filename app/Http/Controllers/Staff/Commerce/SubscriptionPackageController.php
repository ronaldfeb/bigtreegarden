<?php

namespace App\Http\Controllers\Staff\Commerce;

use App\Http\Controllers\Staff\Controller;
use App\Http\Requests\Staff\Commerce\StoreSubscriptionPackageRequest;
use App\Http\Requests\Staff\Commerce\UpdateSubscriptionPackageRequest;
use App\Models\SubscriptionPackage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class SubscriptionPackageController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('manage-commerce');

        return Inertia::render('staff/commerce/subscription-packages/Index', [
            'packages' => SubscriptionPackage::query()->withCount('features')->orderBy('sort_order')->paginate(20),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('manage-commerce');

        return Inertia::render('staff/commerce/subscription-packages/Create');
    }

    public function store(StoreSubscriptionPackageRequest $request): RedirectResponse
    {
        Gate::authorize('manage-commerce');

        $validated = $request->validated();
        $package = SubscriptionPackage::query()->create([
            ...$validated,
            'slug' => $this->uniqueSlug($validated['name'], SubscriptionPackage::class),
        ]);

        return redirect()->route('staff.commerce.subscription-packages.show', $package);
    }

    public function show(SubscriptionPackage $subscriptionPackage): Response
    {
        Gate::authorize('manage-commerce');

        $subscriptionPackage->load('features');

        return Inertia::render('staff/commerce/subscription-packages/Show', ['package' => $subscriptionPackage]);
    }

    public function edit(SubscriptionPackage $subscriptionPackage): Response
    {
        Gate::authorize('manage-commerce');

        return Inertia::render('staff/commerce/subscription-packages/Edit', ['package' => $subscriptionPackage]);
    }

    public function update(UpdateSubscriptionPackageRequest $request, SubscriptionPackage $subscriptionPackage): RedirectResponse
    {
        Gate::authorize('manage-commerce');

        $validated = $request->validated();
        if (isset($validated['name']) && $validated['name'] !== $subscriptionPackage->name) {
            $validated['slug'] = $this->uniqueSlug($validated['name'], SubscriptionPackage::class, $subscriptionPackage->id);
        }

        $subscriptionPackage->update($validated);

        return redirect()->route('staff.commerce.subscription-packages.show', $subscriptionPackage);
    }

    public function destroy(SubscriptionPackage $subscriptionPackage): RedirectResponse
    {
        Gate::authorize('manage-commerce');

        $subscriptionPackage->delete();

        return redirect()->route('staff.commerce.subscription-packages.index');
    }
}
