<?php

namespace App\Http\Controllers\Provider;

use App\Http\Requests\Provider\StoreBackgroundRequest;
use App\Models\ServiceProviderBackground;
use App\Support\MediaStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class BackgroundController extends Controller
{
    public function index(Request $request): Response
    {
        $serviceProvider = $this->currentProvider($request);

        $backgrounds = $serviceProvider->backgrounds()
            ->get()
            ->map(fn (ServiceProviderBackground $background): array => [
                'id' => $background->id,
                'name' => $background->name,
                'image_path' => $background->image_path,
                'image_url' => MediaStorage::url($background->image_path),
                'sort_order' => $background->sort_order,
            ]);

        return Inertia::render('provider/Backgrounds', [
            'backgrounds' => $backgrounds,
        ]);
    }

    public function store(StoreBackgroundRequest $request): RedirectResponse
    {
        $serviceProvider = $this->currentProvider($request);

        $imagePath = $request->file('image')
            ->store("providers/{$serviceProvider->id}/backgrounds", MediaStorage::disk());

        $serviceProvider->backgrounds()->create([
            'name' => $request->validated('name'),
            'image_path' => $imagePath,
            'sort_order' => ((int) $serviceProvider->backgrounds()->max('sort_order')) + 1,
        ]);

        return redirect()->route('provider.backgrounds.index')
            ->with('status', 'Background added to your library.');
    }

    public function destroy(Request $request, ServiceProviderBackground $background): RedirectResponse
    {
        $serviceProvider = $this->currentProvider($request);
        abort_unless($background->service_provider_id === $serviceProvider->id, 404);

        Storage::disk(MediaStorage::disk())->delete($background->image_path);
        $background->delete();

        return redirect()->route('provider.backgrounds.index')
            ->with('status', 'Background removed.');
    }
}
