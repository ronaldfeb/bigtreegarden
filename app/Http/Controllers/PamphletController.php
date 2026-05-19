<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePamphletRequest;
use App\Models\Background;
use App\Models\Pamphlet;
use App\Services\BackgroundRecommendationService;
use App\Services\GuestPamphletDraftService;
use App\Services\QrCodeService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

class PamphletController extends Controller
{
    public function create(): Response
    {
        $backgrounds = Background::query()
            ->with('backgroundCollection')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Background $background): array => [
                'id' => $background->id,
                'name' => $background->name,
                'asset_path' => $background->asset_path,
                'collection_slug' => $background->backgroundCollection?->slug,
            ]);

        return Inertia::render('pamphlets/CreatePamphlet', [
            'backgrounds' => $backgrounds,
            'recommendedCollection' => null,
            'canRegister' => Features::enabled(Features::registration()),
        ]);
    }

    public function store(
        StorePamphletRequest $request,
        BackgroundRecommendationService $backgroundRecommendationService,
        GuestPamphletDraftService $guestPamphletDraftService
    ): RedirectResponse {
        $validated = $request->validated();
        $imagePath = $request->file('image')->store('pamphlets/images', 'public');
        $isAuthenticated = $request->user() !== null;

        $pamphlet = Pamphlet::query()->create([
            'user_id' => $request->user()?->id,
            'background_id' => $validated['background_id'],
            'status' => config('memorial.bypass_payment_for_publish') ? 'published' : 'draft',
            'heading' => $validated['heading'],
            'person_full_name' => $validated['person_full_name'],
            'date_of_birth' => $validated['date_of_birth'],
            'date_of_passing' => $validated['date_of_passing'],
            'date_format' => $validated['date_format'],
            'image_shape' => $validated['image_shape'],
            'image_crop_mode' => $validated['image_crop_mode'],
            'short_text' => $validated['short_text'],
            'uploaded_image_path' => $imagePath,
        ]);

        $pamphlet->pamphletStyle()->updateOrCreate([], [
            'font_family' => $validated['font_family'] ?? 'Georgia',
            'is_bold' => false,
            'is_italic' => false,
            'date_format' => $validated['date_format'],
        ]);

        $response = redirect()->route('pamphlets.show', $pamphlet)->with('status', [
            'recommended_collection' => $backgroundRecommendationService->recommendedCollectionSlug($validated['date_of_birth']),
        ]);

        if (! $isAuthenticated) {
            $token = $guestPamphletDraftService->issueTokenForPamphlet($pamphlet);
            $response->withCookie($guestPamphletDraftService->cookieFromToken($token));
        }

        return $response;
    }

    public function show(Pamphlet $pamphlet, GuestPamphletDraftService $guestPamphletDraftService): Response
    {
        abort_unless($guestPamphletDraftService->requestOwnsPamphlet(request(), $pamphlet), 403);

        $pamphlet->load(['background', 'payments', 'pamphletQrCode']);

        return Inertia::render('pamphlets/Show', [
            'pamphlet' => $pamphlet,
        ]);
    }

    public function print(Pamphlet $pamphlet, QrCodeService $qrCodeService): Response
    {
        abort_if($pamphlet->user_id !== request()->user()->id, 403);

        $targetUrl = route('memorial.public.show', $pamphlet->public_slug);

        $pamphlet->pamphletQrCode()->updateOrCreate([], [
            'target_url' => $targetUrl,
            'image_path' => $qrCodeService->imageUrlForPamphlet($pamphlet),
            'generated_at' => now(),
        ]);

        $pamphlet->load([
            'background',
            'pamphletStyle',
            'memorialPage',
            'pamphletQrCode',
        ]);

        return Inertia::render('pamphlets/Print', [
            'pamphlet' => [
                'id' => $pamphlet->id,
                'heading' => $pamphlet->heading,
                'person_full_name' => $pamphlet->person_full_name,
                'date_of_birth' => optional($pamphlet->date_of_birth)->toDateString(),
                'date_of_passing' => optional($pamphlet->date_of_passing)->toDateString(),
                'date_format' => $pamphlet->date_format,
                'short_text' => $pamphlet->short_text,
                'uploaded_image_path' => $pamphlet->uploaded_image_path,
                'image_shape' => $pamphlet->image_shape,
                'image_crop_mode' => $pamphlet->image_crop_mode,
                'background_asset_path' => $pamphlet->background?->asset_path,
                'font_family' => $pamphlet->pamphletStyle?->font_family ?? 'Georgia',
                'is_bold' => (bool) ($pamphlet->pamphletStyle?->is_bold ?? false),
                'is_italic' => (bool) ($pamphlet->pamphletStyle?->is_italic ?? false),
                'qr_code_image_path' => $pamphlet->pamphletQrCode?->image_path,
                'qr_code_target_url' => $pamphlet->pamphletQrCode?->target_url,
            ],
        ]);
    }
}
