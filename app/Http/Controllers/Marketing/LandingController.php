<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\Ambassador;
use App\Models\AmbassadorImage;
use App\Models\Partner;
use App\Models\Testimonial;
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
            ->with(['images' => fn ($query) => $query->orderBy('sort_order')])
            ->orderBy('name')
            ->get()
            ->map(fn (Ambassador $ambassador): array => [
                'id' => $ambassador->id,
                'name' => $ambassador->name,
                'title' => $ambassador->title,
                'images' => $ambassador->images->map(fn (AmbassadorImage $image): array => [
                    'id' => $image->id,
                    'image_path' => $image->image_path,
                    'caption' => $image->caption,
                ])->values()->all(),
            ]);

        $testimonials = Testimonial::query()
            ->published()
            ->featured()
            ->orderBy('sort_order')
            ->get(['id', 'name', 'photo_path', 'role_or_location', 'body', 'rating'])
            ->map(fn (Testimonial $testimonial): array => [
                'id' => $testimonial->id,
                'name' => $testimonial->name,
                'photo_path' => $testimonial->photo_path,
                'role_or_location' => $testimonial->role_or_location,
                'body' => $testimonial->body,
                'rating' => $testimonial->rating,
            ]);

        $partners = Partner::query()
            ->active()
            ->orderBy('sort_order')
            ->get(['id', 'name', 'logo_path', 'website_url'])
            ->map(fn (Partner $partner): array => [
                'id' => $partner->id,
                'name' => $partner->name,
                'logo_path' => $partner->logo_path,
                'website_url' => $partner->website_url,
            ]);

        return Inertia::render('marketing/Landing', [
            'canRegister' => Features::enabled(Features::registration()),
            'ambassadors' => $ambassadors,
            'testimonials' => $testimonials,
            'partners' => $partners,
        ]);
    }
}
