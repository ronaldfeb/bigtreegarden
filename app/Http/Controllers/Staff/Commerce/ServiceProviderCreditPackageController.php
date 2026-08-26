<?php

namespace App\Http\Controllers\Staff\Commerce;

use App\Http\Controllers\Staff\Controller;
use App\Http\Requests\Staff\Commerce\StoreServiceProviderCreditPackageRequest;
use App\Http\Requests\Staff\Commerce\UpdateServiceProviderCreditPackageRequest;
use App\Models\ServiceProviderCreditPackage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ServiceProviderCreditPackageController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('manage-commerce');

        return Inertia::render('staff/commerce/credit-packages/Index', [
            'packages' => ServiceProviderCreditPackage::query()->orderBy('sort_order')->paginate(20),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('manage-commerce');

        return Inertia::render('staff/commerce/credit-packages/Create');
    }

    public function store(StoreServiceProviderCreditPackageRequest $request): RedirectResponse
    {
        Gate::authorize('manage-commerce');

        $validated = $request->validated();
        $package = ServiceProviderCreditPackage::query()->create([
            ...$validated,
            'currency' => $validated['currency'] ?? 'ZAR',
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $validated['sort_order'] ?? 0,
            'slug' => $this->uniqueSlug($validated['name'], ServiceProviderCreditPackage::class),
        ]);

        return redirect()->route('staff.commerce.credit-packages.show', $package);
    }

    public function show(ServiceProviderCreditPackage $creditPackage): Response
    {
        Gate::authorize('manage-commerce');

        return Inertia::render('staff/commerce/credit-packages/Show', [
            'package' => $creditPackage,
        ]);
    }

    public function edit(ServiceProviderCreditPackage $creditPackage): Response
    {
        Gate::authorize('manage-commerce');

        return Inertia::render('staff/commerce/credit-packages/Edit', [
            'package' => $creditPackage,
        ]);
    }

    public function update(
        UpdateServiceProviderCreditPackageRequest $request,
        ServiceProviderCreditPackage $creditPackage,
    ): RedirectResponse {
        Gate::authorize('manage-commerce');

        $validated = $request->validated();

        if (isset($validated['name']) && $validated['name'] !== $creditPackage->name) {
            $validated['slug'] = $this->uniqueSlug(
                $validated['name'],
                ServiceProviderCreditPackage::class,
                $creditPackage->id,
            );
        }

        $creditPackage->update([
            ...$validated,
            'currency' => $validated['currency'] ?? $creditPackage->currency,
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $validated['sort_order'] ?? $creditPackage->sort_order,
        ]);

        return redirect()->route('staff.commerce.credit-packages.show', $creditPackage);
    }

    public function destroy(ServiceProviderCreditPackage $creditPackage): RedirectResponse
    {
        Gate::authorize('manage-commerce');

        $creditPackage->delete();

        return redirect()->route('staff.commerce.credit-packages.index');
    }
}
