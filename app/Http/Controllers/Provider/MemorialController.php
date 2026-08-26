<?php

namespace App\Http\Controllers\Provider;

use App\Http\Requests\Provider\StoreMemorialRequest;
use App\Models\MemorialPagePamphlet;
use App\Models\MemorialPagePamphletBackground;
use App\Models\ServiceProviderBackground;
use App\Services\ProviderMemorialCreationService;
use App\Support\MediaStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

class MemorialController extends Controller
{
    public function index(Request $request): Response
    {
        $serviceProvider = $this->currentProvider($request);

        $memorials = MemorialPagePamphlet::query()
            ->whereHas('memorialPage.personOfInterest', function ($query) use ($serviceProvider): void {
                $query->where('service_provider_id', $serviceProvider->id);
            })
            ->with(['memorialPage.personOfInterest', 'background', 'serviceProviderBackground'])
            ->latest()
            ->paginate(15)
            ->through(fn (MemorialPagePamphlet $pamphlet): array => [
                'id' => $pamphlet->id,
                'heading' => $pamphlet->heading,
                'person_full_name' => $pamphlet->person_full_name,
                'status' => $pamphlet->status?->value ?? $pamphlet->status,
                'public_slug' => $pamphlet->public_slug,
                'created_at' => $pamphlet->created_at?->toIso8601String(),
                'edit_url' => route('memorial.edit', $pamphlet),
                'public_url' => route('memorial.public.show', $pamphlet->public_slug),
            ]);

        return Inertia::render('provider/Memorials/Index', [
            'memorials' => $memorials,
            'creditsRemaining' => $serviceProvider->credits_remaining,
        ]);
    }

    public function create(Request $request): Response|RedirectResponse
    {
        $serviceProvider = $this->currentProvider($request);

        if ($serviceProvider->credits_remaining < 1) {
            return redirect()->route('provider.credits.index')
                ->with('status', 'You need memorial page credits before creating a page.');
        }

        return Inertia::render('provider/Memorials/Create', [
            'creditsRemaining' => $serviceProvider->credits_remaining,
            'catalogueBackgrounds' => MemorialPagePamphletBackground::query()
                ->where('is_active', true)
                ->with('collection:id,slug,name')
                ->orderBy('sort_order')
                ->get()
                ->map(fn (MemorialPagePamphletBackground $background): array => [
                    'id' => $background->id,
                    'name' => $background->name,
                    'asset_path' => MediaStorage::url($background->image_path) ?? $background->image_path,
                    'collection_slug' => $background->collection?->slug ?? 'catalogue',
                    'source' => 'catalogue',
                ]),
            'providerBackgrounds' => $serviceProvider->backgrounds()
                ->get()
                ->map(fn (ServiceProviderBackground $background): array => [
                    'id' => $background->id,
                    'name' => $background->name,
                    'asset_path' => MediaStorage::url($background->image_path),
                    'collection_slug' => 'your-library',
                    'source' => 'provider',
                ]),
        ]);
    }

    public function store(
        StoreMemorialRequest $request,
        ProviderMemorialCreationService $creationService,
    ): RedirectResponse {
        $serviceProvider = $this->currentProvider($request);

        if ($serviceProvider->credits_remaining < 1) {
            throw ValidationException::withMessages([
                'credits' => 'You do not have enough memorial page credits.',
            ]);
        }

        $validated = $request->validated();

        if (($validated['background_source'] ?? null) === 'provider') {
            $ownsBackground = ServiceProviderBackground::query()
                ->where('service_provider_id', $serviceProvider->id)
                ->whereKey($validated['service_provider_background_id'])
                ->exists();

            abort_unless($ownsBackground, 422);
        }

        $imagePath = $request->file('image')->storePublicly(
            'pamphlets/images',
            MediaStorage::disk(),
        );

        try {
            $pamphlet = $creationService->create(
                $serviceProvider,
                $request->user(),
                $validated,
                $imagePath,
            );
        } catch (RuntimeException $exception) {
            throw ValidationException::withMessages([
                'credits' => $exception->getMessage(),
            ]);
        }

        return redirect()->route('provider.memorials.show', $pamphlet)
            ->with('status', 'Memorial page created. One credit was used.');
    }

    public function show(Request $request, MemorialPagePamphlet $pamphlet): Response
    {
        $this->assertProviderOwnsPamphlet($request, $pamphlet);

        $pamphlet->load([
            'background',
            'serviceProviderBackground',
            'style',
            'memorialPage.personOfInterest.users',
        ]);

        $owner = $pamphlet->owner();

        return Inertia::render('provider/Memorials/Show', [
            'pamphlet' => [
                'id' => $pamphlet->id,
                'heading' => $pamphlet->heading,
                'person_full_name' => $pamphlet->person_full_name,
                'status' => $pamphlet->status?->value ?? $pamphlet->status,
                'public_slug' => $pamphlet->public_slug,
                'short_text' => $pamphlet->short_text,
                'family_owner' => $owner?->only(['id', 'name', 'email']),
                'edit_url' => route('memorial.edit', $pamphlet),
                'public_url' => route('memorial.public.show', $pamphlet->public_slug),
                'background_asset_path' => MediaStorage::url(
                    $pamphlet->serviceProviderBackground?->image_path
                        ?? $pamphlet->background?->image_path,
                ),
            ],
        ]);
    }

    public function destroy(Request $request, MemorialPagePamphlet $pamphlet): RedirectResponse
    {
        $this->assertProviderOwnsPamphlet($request, $pamphlet);

        $pamphlet->delete();
        // Soft-delete does not restore credits (by design).

        return redirect()->route('provider.memorials.index')
            ->with('status', 'Memorial page deleted. Credits are not refunded.');
    }

    private function assertProviderOwnsPamphlet(Request $request, MemorialPagePamphlet $pamphlet): void
    {
        $serviceProvider = $this->currentProvider($request);
        $pamphlet->loadMissing('memorialPage.personOfInterest');

        abort_unless(
            $pamphlet->memorialPage?->personOfInterest?->service_provider_id === $serviceProvider->id,
            404,
        );
    }
}
