<?php

namespace App\Http\Controllers\Provider;

use App\Http\Requests\Provider\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function edit(Request $request): Response
    {
        $serviceProvider = $this->currentProvider($request);

        return Inertia::render('provider/Profile', [
            'serviceProvider' => $serviceProvider->only([
                'id', 'name', 'slug', 'status', 'registration_number', 'description',
                'logo_path', 'cover_image_path', 'email', 'phone', 'website_url',
                'physical_address', 'city', 'province',
            ]),
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $serviceProvider = $this->currentProvider($request);
        $validated = $request->safe()->except(['logo', 'cover_image']);

        if ($request->hasFile('logo')) {
            if ($serviceProvider->logo_path !== null) {
                Storage::disk('public')->delete($serviceProvider->logo_path);
            }

            $validated['logo_path'] = $request->file('logo')
                ->store("providers/{$serviceProvider->id}", 'public');
        }

        if ($request->hasFile('cover_image')) {
            if ($serviceProvider->cover_image_path !== null) {
                Storage::disk('public')->delete($serviceProvider->cover_image_path);
            }

            $validated['cover_image_path'] = $request->file('cover_image')
                ->store("providers/{$serviceProvider->id}", 'public');
        }

        $serviceProvider->update($validated);

        return redirect()->route('provider.profile.edit');
    }
}
