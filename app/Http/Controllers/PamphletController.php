<?php

namespace App\Http\Controllers;

use App\Enums\MemorialPageStatus;
use App\Enums\PamphletStatus;
use App\Enums\PersonOfInterestStatus;
use App\Http\Requests\StorePamphletRequest;
use App\Http\Requests\UpdatePamphletRequest;
use App\Models\MemorialPagePamphlet;
use App\Models\MemorialPagePamphletBackground;
use App\Models\PersonOfInterest;
use App\Models\SubscriptionPackage;
use App\Services\BackgroundRecommendationService;
use App\Services\GuestPamphletDraftService;
use App\Services\QrCodeService;
use App\Support\MediaStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

class PamphletController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('pamphlets/CreatePamphlet', [
            'backgrounds' => $this->backgroundOptions(),
            'recommendedCollection' => null,
            'canRegister' => Features::enabled(Features::registration()),
            'pamphlet' => null,
        ]);
    }

    public function store(
        StorePamphletRequest $request,
        BackgroundRecommendationService $backgroundRecommendationService,
        GuestPamphletDraftService $guestPamphletDraftService
    ): RedirectResponse {
        $validated = $request->validated();
        $imagePath = $request->file('image')->storePublicly('pamphlets/images', $this->mediaDisk());
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
            'heading_color' => $validated['heading_color'],
            'name_color' => $validated['name_color'],
            'short_text_color' => $validated['short_text_color'],
            'dates_color' => $validated['dates_color'],
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

        $pamphlet->load(['background', 'style', 'transactions', 'memorialPage.personOfInterest']);

        return Inertia::render('pamphlets/Show', [
            'pamphlet' => $this->pamphletPayload($pamphlet),
            'pricing' => $this->memorialPricingPayload(),
            'canRegister' => Features::enabled(Features::registration()),
        ]);
    }

    public function edit(MemorialPagePamphlet $pamphlet, GuestPamphletDraftService $guestPamphletDraftService): Response
    {
        abort_unless($guestPamphletDraftService->requestOwnsPamphlet(request(), $pamphlet), 403);
        abort_unless(in_array($pamphlet->status, [PamphletStatus::Draft, PamphletStatus::PendingPayment], true), 403);

        $pamphlet->load(['background', 'style', 'memorialPage.personOfInterest']);

        return Inertia::render('pamphlets/CreatePamphlet', [
            'backgrounds' => $this->backgroundOptions(),
            'recommendedCollection' => null,
            'canRegister' => Features::enabled(Features::registration()),
            'pamphlet' => $this->editPamphletPayload($pamphlet),
        ]);
    }

    public function update(
        UpdatePamphletRequest $request,
        MemorialPagePamphlet $pamphlet,
        GuestPamphletDraftService $guestPamphletDraftService
    ): RedirectResponse {
        abort_unless($guestPamphletDraftService->requestOwnsPamphlet(request(), $pamphlet), 403);
        abort_unless(in_array($pamphlet->status, [PamphletStatus::Draft, PamphletStatus::PendingPayment], true), 403);

        $validated = $request->validated();
        $personOfInterest = $pamphlet->memorialPage?->personOfInterest;
        abort_if($personOfInterest === null, 404);

        [$firstName, $lastName] = $this->splitFullName($validated['person_full_name']);

        $imagePath = $pamphlet->uploaded_image_path;

        if ($request->hasFile('image')) {
            $newPath = $request->file('image')->storePublicly('pamphlets/images', $this->mediaDisk());

            if ($imagePath !== null && $imagePath !== '' && Storage::disk($this->mediaDisk())->exists($imagePath)) {
                Storage::disk($this->mediaDisk())->delete($imagePath);
            }

            $imagePath = $newPath;
        }

        $personOfInterest->update([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'display_name' => $validated['person_full_name'],
            'date_of_birth' => $validated['date_of_birth'],
            'date_of_passing' => $validated['date_of_passing'],
            'profile_image_path' => $imagePath,
        ]);

        $pamphlet->memorialPage?->update([
            'title' => $validated['heading'],
        ]);

        $pamphlet->update([
            'background_id' => $validated['background_id'],
            'heading' => $validated['heading'],
            'short_text' => $validated['short_text'],
            'date_format' => $validated['date_format'],
            'image_shape' => $validated['image_shape'],
            'image_crop_mode' => $validated['image_crop_mode'],
            'uploaded_image_path' => $imagePath,
        ]);

        $pamphlet->style()->updateOrCreate([], [
            'font_family' => $validated['font_family'] ?? 'Georgia',
            'date_format' => $validated['date_format'],
            'heading_color' => $validated['heading_color'],
            'name_color' => $validated['name_color'],
            'short_text_color' => $validated['short_text_color'],
            'dates_color' => $validated['dates_color'],
        ]);

        return redirect()->route('pamphlets.show', $pamphlet);
    }

    public function print(MemorialPagePamphlet $pamphlet, QrCodeService $qrCodeService): Response
    {
        abort_unless($pamphlet->canBeManagedBy(request()->user()), 403);

        $personOfInterest = $pamphlet->memorialPage?->personOfInterest;
        $targetUrl = route('memorial.public.show', $pamphlet->public_slug);

        $personOfInterest?->update([
            'qr_code_path' => $qrCodeService->imageUrlForTarget($targetUrl),
            'qr_generated_at' => now(),
        ]);

        $pamphlet->load([
            'background',
            'style',
            'transactions',
            'memorialPage.personOfInterest',
        ]);

        return Inertia::render('pamphlets/Print', [
            'pamphlet' => $this->pamphletPayload($pamphlet),
        ]);
    }

    /**
     * @return list<array{id: string, name: string, asset_path: string, collection_slug: string|null}>
     */
    private function backgroundOptions(): array
    {
        return MemorialPagePamphletBackground::query()
            ->with('collection')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (MemorialPagePamphletBackground $background): array => [
                'id' => $background->id,
                'name' => $background->name,
                'asset_path' => $background->asset_path,
                'collection_slug' => $background->collection?->slug,
            ])
            ->all();
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
            'uploaded_image_url' => MediaStorage::url($pamphlet->uploaded_image_path),
            'public_slug' => $pamphlet->public_slug,
            'status' => $pamphlet->status?->value ?? $pamphlet->status,
            'paid_at' => optional($pamphlet->paid_at)?->toIso8601String(),
            'background' => $pamphlet->background,
            'background_asset_path' => $pamphlet->background?->asset_path,
            'font_family' => $pamphlet->style?->font_family ?? 'Georgia',
            'heading_color' => $pamphlet->style?->heading_color ?? '#000000',
            'name_color' => $pamphlet->style?->name_color ?? '#000000',
            'short_text_color' => $pamphlet->style?->short_text_color ?? '#000000',
            'dates_color' => $pamphlet->style?->dates_color ?? '#000000',
            'transactions' => $pamphlet->transactions,
            'pamphlet_qr_code' => $pamphlet->memorialPage?->personOfInterest ? [
                'target_url' => route('memorial.public.show', $pamphlet->public_slug),
                'image_path' => $pamphlet->memorialPage->personOfInterest->qr_code_path,
            ] : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function editPamphletPayload(MemorialPagePamphlet $pamphlet): array
    {
        return [
            'id' => $pamphlet->id,
            'heading' => $pamphlet->heading,
            'person_full_name' => $pamphlet->person_full_name,
            'date_of_birth' => optional($pamphlet->date_of_birth)->toDateString(),
            'date_of_passing' => optional($pamphlet->date_of_passing)->toDateString(),
            'date_format' => $pamphlet->date_format ?? 'd M Y',
            'image_shape' => $pamphlet->image_shape ?? 'square',
            'image_crop_mode' => $pamphlet->image_crop_mode ?? 'cover',
            'short_text' => $pamphlet->short_text,
            'background_id' => $pamphlet->background_id,
            'uploaded_image_url' => MediaStorage::url($pamphlet->uploaded_image_path),
            'font_family' => $pamphlet->style?->font_family ?? 'Georgia',
            'heading_color' => $pamphlet->style?->heading_color ?? '#000000',
            'name_color' => $pamphlet->style?->name_color ?? '#000000',
            'short_text_color' => $pamphlet->style?->short_text_color ?? '#000000',
            'dates_color' => $pamphlet->style?->dates_color ?? '#000000',
        ];
    }

    /**
     * @return array{name: string, price_cents: int, currency: string, billing_interval: string}|null
     */
    private function memorialPricingPayload(): ?array
    {
        $package = SubscriptionPackage::memorialPagePackage();

        if ($package === null) {
            return null;
        }

        return [
            'name' => $package->name,
            'price_cents' => $package->price_cents,
            'currency' => $package->currency,
            'billing_interval' => $package->billing_interval,
        ];
    }

    private function mediaDisk(): string
    {
        return MediaStorage::disk();
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
