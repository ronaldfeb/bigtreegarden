<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

class PartnerDirectoryController extends Controller
{
    public function index(): Response
    {
        $partners = Partner::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'logo_path', 'website_url'])
            ->map(fn (Partner $partner): array => [
                'id' => $partner->id,
                'name' => $partner->name,
                'logo_path' => $partner->logo_path,
                'website_url' => $partner->website_url,
            ]);

        return Inertia::render('marketing/Partners/Index', [
            'canRegister' => Features::enabled(Features::registration()),
            'partners' => $partners,
        ]);
    }
}
