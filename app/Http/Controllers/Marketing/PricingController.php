<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPackage;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

class PricingController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('marketing/Pricing', [
            'canRegister' => Features::enabled(Features::registration()),
            'packages' => SubscriptionPackage::marketingOfferings()->values()->all(),
        ]);
    }
}
