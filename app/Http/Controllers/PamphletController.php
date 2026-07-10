<?php

namespace App\Http\Controllers;

use App\Enums\MemorialPageStatus;
use App\Enums\PamphletStatus;
use App\Enums\PersonOfInterestStatus;
use App\Http\Requests\StorePamphletRequest;
use App\Models\MemorialPagePamphlet;
use App\Models\MemorialPagePamphletBackground;
use App\Models\PersonOfInterest;
use App\Services\BackgroundRecommendationService;
use App\Services\GuestPamphletDraftService;
use App\Services\QrCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

class PamphletController extends Controller
{
    public function create(): Response
    {
        $backgrounds = MemorialPagePamphletBackground::query()
            ->with('collection')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (MemorialPagePamphletBackground $background): array => [
                'id' => $background->id,
                'name' => $background->name,
                'asset_path' => $background->asset_path,
                'collection_slug' => $background->collection?->slug,
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
        [$firstName, $lastName] = $this->splitFullName($validated['person_full_name']);

        $status = config('memorial.bypass_payment_for_publish')
            ? PamphletStatus::Published
            : PamphletStatus::Draft;

        $personOfInterest = PersonOfInterest::query()->create([
            'created_by_user_id' => $request->user()?->id,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'display_name' => $validated['person_full_name'],
            'date_of_birth' => $validated['date_of_birth'],
            'date_of_passing' => $validated['date_of_passing'],
            'profile_image_path' => $imagePath,
            'public_slug' => Str::lower((string) Str::ulid()),
            'status' => PersonOfInterestStatus::Draft,
        ]);

        if ($request->user() !== null) {
            $personOfInterest->users()->attach($request->user()->id, ['role' => 'owner']);
        }

        $memorialPage = $personOfInterest->memorialPages()->create([
            'title' => $validated['heading'],
            'public_slug' => Str::lower((string) Str::ulid()),
            'status' => $status === PamphletStatus::Published ? MemorialPageStatus::Published : MemorialPageStatus::Draft,
            'published_at' => $status === PamphletStatus::Published ? now() : null,
        ]);

        $pamphlet = $memorialPage->pamphlet()->create([
            'background_id' => $validated['background_id'],
            'status' => $status,
            'heading' => $validated['heading'],
            'short_text' => $validated['short_text'],
            'date_format' => $validated['date_format'],
            'image_shape' => $validated['image_shape'],
            'image_crop_mode' => $validated['image_crop_mode'],
            'uploaded_image_path' => $imagePath,
        ]);

        $pamphlet->style()->create([
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

    public function show(MemorialPagePamphlet $pamphlet, GuestPamphletDraftService $guestPamphletDraftService): Response
    {
        abort_unless($guestPamphletDraftService->requestOwnsPamphlet(request(), $pamphlet), 403);

        $pamphlet->load(['background', 'transactions', 'memorialPage.personOfInterest']);

        return Inertia::render('pamphlets/Show', [
            'pamphlet' => $this->pamphletPayload($pamphlet),
        ]);
    }

    public function print(MemorialPagePamphlet $pamphlet, QrCodeService $qrCodeService): Response
    {
        abort_unless($pamphlet->isOwnedBy(request()->user()), 403);

        $personOfInterest = $pamphlet->memorialPage?->personOfInterest;
        $targetUrl = route('memorial.public.show', $pamphlet->public_slug);

        $personOfInterest?->update([
            'qr_code_path' => $qrCodeService->imageUrlForTarget($targetUrl),
            'qr_generated_at' => now(),
        ]);

        $pamphlet->load([
            'background',
            'style',
            'memorialPage.personOfInterest',
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
                'font_family' => $pamphlet->style?->font_family ?? 'Georgia',
                'is_bold' => (bool) ($pamphlet->style?->is_bold ?? false),
                'is_italic' => (bool) ($pamphlet->style?->is_italic ?? false),
                'qr_code_image_path' => $personOfInterest?->qr_code_path,
                'qr_code_target_url' => $targetUrl,
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function pamphletPayload(MemorialPagePamphlet $pamphlet): array
    {
        return [
            'id' => $pamphlet->id,
            'heading' => $pamphlet->heading,
            'person_full_name' => $pamphlet->person_full_name,
            'date_of_birth' => optional($pamphlet->date_of_birth)->toDateString(),
            'date_of_passing' => optional($pamphlet->date_of_passing)->toDateString(),
            'date_format' => $pamphlet->date_format,
            'image_shape' => $pamphlet->image_shape,
            'image_crop_mode' => $pamphlet->image_crop_mode,
            'short_text' => $pamphlet->short_text,
            'uploaded_image_path' => $pamphlet->uploaded_image_path,
            'public_slug' => $pamphlet->public_slug,
            'status' => $pamphlet->status?->value ?? $pamphlet->status,
            'paid_at' => optional($pamphlet->paid_at)?->toIso8601String(),
            'background' => $pamphlet->background,
            'transactions' => $pamphlet->transactions,
            'pamphlet_qr_code' => $pamphlet->memorialPage?->personOfInterest ? [
                'target_url' => route('memorial.public.show', $pamphlet->public_slug),
                'image_path' => $pamphlet->memorialPage->personOfInterest->qr_code_path,
            ] : null,
        ];
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function splitFullName(string $fullName): array
    {
        $parts = preg_split('/\s+/', trim($fullName), 2);

        return [
            $parts[0] ?? $fullName,
            $parts[1] ?? '',
        ];
    }
}
