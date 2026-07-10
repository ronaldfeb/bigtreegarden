<?php

namespace App\Http\Controllers\Provider;

use App\Http\Requests\Provider\StoreSocialMediaRequest;
use App\Http\Requests\Provider\UpdateSocialMediaRequest;
use App\Models\ServiceProviderSocialMedia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SocialMediaController extends Controller
{
    public function index(Request $request): Response
    {
        $serviceProvider = $this->currentProvider($request);

        return Inertia::render('provider/SocialMedia', [
            'socialMedia' => $serviceProvider->socialMedia()->get(),
        ]);
    }

    public function store(StoreSocialMediaRequest $request): RedirectResponse
    {
        $serviceProvider = $this->currentProvider($request);

        $serviceProvider->socialMedia()->create($request->validated());

        return redirect()->route('provider.social-media.index');
    }

    public function update(UpdateSocialMediaRequest $request, ServiceProviderSocialMedia $socialMedia): RedirectResponse
    {
        $serviceProvider = $this->currentProvider($request);
        abort_unless($socialMedia->service_provider_id === $serviceProvider->id, 404);

        $socialMedia->update($request->validated());

        return redirect()->route('provider.social-media.index');
    }

    public function destroy(Request $request, ServiceProviderSocialMedia $socialMedia): RedirectResponse
    {
        $serviceProvider = $this->currentProvider($request);
        abort_unless($socialMedia->service_provider_id === $serviceProvider->id, 404);

        $socialMedia->delete();

        return redirect()->route('provider.social-media.index');
    }
}
