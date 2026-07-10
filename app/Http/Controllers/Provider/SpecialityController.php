<?php

namespace App\Http\Controllers\Provider;

use App\Http\Requests\Provider\StoreSpecialityRequest;
use App\Http\Requests\Provider\UpdateSpecialityRequest;
use App\Models\ServiceProviderSpeciality;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SpecialityController extends Controller
{
    public function index(Request $request): Response
    {
        $serviceProvider = $this->currentProvider($request);

        return Inertia::render('provider/Specialities', [
            'specialities' => $serviceProvider->specialities()->get(),
        ]);
    }

    public function store(StoreSpecialityRequest $request): RedirectResponse
    {
        $serviceProvider = $this->currentProvider($request);
        $validated = $request->validated();

        $serviceProvider->specialities()->create([
            ...$validated,
            'sort_order' => $validated['sort_order'] ?? ((int) $serviceProvider->specialities()->max('sort_order')) + 1,
        ]);

        return redirect()->route('provider.specialities.index');
    }

    public function update(UpdateSpecialityRequest $request, ServiceProviderSpeciality $speciality): RedirectResponse
    {
        $serviceProvider = $this->currentProvider($request);
        abort_unless($speciality->service_provider_id === $serviceProvider->id, 404);

        $speciality->update($request->validated());

        return redirect()->route('provider.specialities.index');
    }

    public function destroy(Request $request, ServiceProviderSpeciality $speciality): RedirectResponse
    {
        $serviceProvider = $this->currentProvider($request);
        abort_unless($speciality->service_provider_id === $serviceProvider->id, 404);

        $speciality->delete();

        return redirect()->route('provider.specialities.index');
    }
}
