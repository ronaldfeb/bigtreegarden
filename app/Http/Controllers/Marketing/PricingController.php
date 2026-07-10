<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPackage;
use App\Models\SubscriptionPackageFeature;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

class PricingController extends Controller
{
    public function index(): Response
    {
        $packages = SubscriptionPackage::query()
            ->active()
            ->with(['features' => fn ($query) => $query->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get()
            ->map(fn (SubscriptionPackage $package): array => [
                'id' => $package->id,
                'name' => $package->name,
                'slug' => $package->slug,
                'description' => $package->description,
                'price_cents' => $package->price_cents,
                'currency' => $package->currency,
                'billing_interval' => $package->billing_interval,
                'is_featured' => $package->is_featured,
                'features' => $package->features->map(fn (SubscriptionPackageFeature $feature): array => [
                    'id' => $feature->id,
                    'label' => $feature->label,
                    'description' => $feature->description,
                    'is_included' => $feature->is_included,
                ])->values()->all(),
            ]);

        return Inertia::render('marketing/Pricing', [
            'canRegister' => Features::enabled(Features::registration()),
            'packages' => $packages,
        ]);
    }
}
