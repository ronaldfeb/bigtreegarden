<?php

namespace App\Http\Controllers\Provider;

use App\Models\ServiceProvider;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $serviceProvider = $this->currentProvider($request);
        $serviceProvider->loadCount(['services', 'specialities', 'socialMedia', 'images']);

        return Inertia::render('provider/Dashboard', [
            'serviceProvider' => $serviceProvider->only([
                'id', 'name', 'slug', 'status', 'logo_path', 'city', 'province',
            ]),
            'counts' => [
                'services' => $serviceProvider->services_count,
                'specialities' => $serviceProvider->specialities_count,
                'social_media' => $serviceProvider->social_media_count,
                'images' => $serviceProvider->images_count,
            ],
            'profileCompleteness' => $this->profileCompleteness($serviceProvider),
        ]);
    }

    /**
     * Percentage (0-100) of optional profile fields that have been filled in.
     */
    private function profileCompleteness(ServiceProvider $serviceProvider): int
    {
        $fields = [
            'name',
            'description',
            'registration_number',
            'email',
            'phone',
            'website_url',
            'physical_address',
            'city',
            'province',
            'logo_path',
            'cover_image_path',
        ];

        $filled = collect($fields)
            ->filter(fn (string $field): bool => filled($serviceProvider->getAttribute($field)))
            ->count();

        return (int) round($filled / count($fields) * 100);
    }
}
