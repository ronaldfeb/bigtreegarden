<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\Ambassador;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

class LandingController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $ambassadors = Ambassador::query()
            ->active()
            ->orderBy('name')
            ->get()
            ->map(fn (Ambassador $ambassador): array => [
                'id' => $ambassador->id,
                'name' => $ambassador->name,
                'title' => $ambassador->title,
                'image_url' => $ambassador->image_url,
            ]);

        return Inertia::render('marketing/Landing', [
            'canRegister' => Features::enabled(Features::registration()),
            'ambassadors' => $ambassadors,
        ]);
    }
}
