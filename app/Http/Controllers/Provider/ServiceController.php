<?php

namespace App\Http\Controllers\Provider;

use App\Http\Requests\Provider\StoreServiceRequest;
use App\Http\Requests\Provider\UpdateServiceRequest;
use App\Models\ServiceProviderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ServiceController extends Controller
{
    public function index(Request $request): Response
    {
        $serviceProvider = $this->currentProvider($request);

        return Inertia::render('provider/Services', [
            'services' => $serviceProvider->services()->get(),
        ]);
    }

    public function store(StoreServiceRequest $request): RedirectResponse
    {
        $serviceProvider = $this->currentProvider($request);
        $validated = $request->validated();

        $serviceProvider->services()->create([
            ...$validated,
            'sort_order' => $validated['sort_order'] ?? ((int) $serviceProvider->services()->max('sort_order')) + 1,
        ]);

        return redirect()->route('provider.services.index');
    }

    public function update(UpdateServiceRequest $request, ServiceProviderService $service): RedirectResponse
    {
        $serviceProvider = $this->currentProvider($request);
        abort_unless($service->service_provider_id === $serviceProvider->id, 404);

        $service->update($request->validated());

        return redirect()->route('provider.services.index');
    }

    public function destroy(Request $request, ServiceProviderService $service): RedirectResponse
    {
        $serviceProvider = $this->currentProvider($request);
        abort_unless($service->service_provider_id === $serviceProvider->id, 404);

        $service->delete();

        return redirect()->route('provider.services.index');
    }
}
