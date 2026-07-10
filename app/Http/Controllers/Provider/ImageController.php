<?php

namespace App\Http\Controllers\Provider;

use App\Http\Requests\Provider\StoreImageRequest;
use App\Models\ServiceProviderImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ImageController extends Controller
{
    public function index(Request $request): Response
    {
        $serviceProvider = $this->currentProvider($request);

        return Inertia::render('provider/Images', [
            'images' => $serviceProvider->images()->get(),
        ]);
    }

    public function store(StoreImageRequest $request): RedirectResponse
    {
        $serviceProvider = $this->currentProvider($request);

        $imagePath = $request->file('image')
            ->store("providers/{$serviceProvider->id}/gallery", 'public');

        $serviceProvider->images()->create([
            'image_path' => $imagePath,
            'caption' => $request->validated('caption'),
            'sort_order' => ((int) $serviceProvider->images()->max('sort_order')) + 1,
        ]);

        return redirect()->route('provider.images.index');
    }

    public function destroy(Request $request, ServiceProviderImage $image): RedirectResponse
    {
        $serviceProvider = $this->currentProvider($request);
        abort_unless($image->service_provider_id === $serviceProvider->id, 404);

        Storage::disk('public')->delete($image->image_path);
        $image->delete();

        return redirect()->route('provider.images.index');
    }
}
