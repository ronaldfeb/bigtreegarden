<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\ServiceProvider;
use App\Models\ServiceProviderImage;
use App\Models\ServiceProviderService;
use App\Models\ServiceProviderSocialMedia;
use App\Models\ServiceProviderSpeciality;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

class ServiceProviderDirectoryController extends Controller
{
    public function index(): Response
    {
        $providers = ServiceProvider::query()
            ->active()
            ->with(['specialities' => fn ($query) => $query->orderBy('sort_order')])
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'description', 'logo_path', 'city', 'province'])
            ->map(fn (ServiceProvider $provider): array => [
                'id' => $provider->id,
                'name' => $provider->name,
                'slug' => $provider->slug,
                'description' => $provider->description,
                'logo_path' => $provider->logo_path,
                'city' => $provider->city,
                'province' => $provider->province,
                'specialities' => $provider->specialities->map(fn (ServiceProviderSpeciality $speciality): array => [
                    'id' => $speciality->id,
                    'name' => $speciality->name,
                ])->values()->all(),
            ]);

        return Inertia::render('marketing/Providers/Index', [
            'canRegister' => Features::enabled(Features::registration()),
            'providers' => $providers,
        ]);
    }

    public function show(ServiceProvider $serviceProvider): Response
    {
        abort_unless(
            $serviceProvider->status === config('constants.service_provider.status.active'),
            404,
        );

        $serviceProvider->load([
            'services' => fn ($query) => $query->orderBy('sort_order'),
            'specialities' => fn ($query) => $query->orderBy('sort_order'),
            'socialMedia',
            'images' => fn ($query) => $query->orderBy('sort_order'),
        ]);

        return Inertia::render('marketing/Providers/Show', [
            'canRegister' => Features::enabled(Features::registration()),
            'provider' => [
                'id' => $serviceProvider->id,
                'name' => $serviceProvider->name,
                'slug' => $serviceProvider->slug,
                'registration_number' => $serviceProvider->registration_number,
                'description' => $serviceProvider->description,
                'logo_path' => $serviceProvider->logo_path,
                'cover_image_path' => $serviceProvider->cover_image_path,
                'email' => $serviceProvider->email,
                'phone' => $serviceProvider->phone,
                'website_url' => $serviceProvider->website_url,
                'physical_address' => $serviceProvider->physical_address,
                'city' => $serviceProvider->city,
                'province' => $serviceProvider->province,
                'services' => $serviceProvider->services->map(fn (ServiceProviderService $service): array => [
                    'id' => $service->id,
                    'name' => $service->name,
                    'description' => $service->description,
                    'price_from_cents' => $service->price_from_cents,
                ])->values()->all(),
                'specialities' => $serviceProvider->specialities->map(fn (ServiceProviderSpeciality $speciality): array => [
                    'id' => $speciality->id,
                    'name' => $speciality->name,
                ])->values()->all(),
                'social_media' => $serviceProvider->socialMedia->map(fn (ServiceProviderSocialMedia $social): array => [
                    'id' => $social->id,
                    'platform' => $social->platform,
                    'url' => $social->url,
                ])->values()->all(),
                'images' => $serviceProvider->images->map(fn (ServiceProviderImage $image): array => [
                    'id' => $image->id,
                    'image_path' => $image->image_path,
                    'caption' => $image->caption,
                ])->values()->all(),
            ],
        ]);
    }
}
