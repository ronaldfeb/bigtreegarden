<?php

namespace App\Http\Controllers;

use App\Enums\MemorialPageStatus;
use App\Enums\PamphletStatus;
use App\Enums\TransactionStatus;
use App\Http\Requests\UpdateMemorialPageRequest;
use App\Models\MemorialPage;
use App\Models\MemorialPageMessage;
use App\Models\MemorialPagePamphlet;
use App\Models\MemorialSite;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class MemorialPageController extends Controller
{
    public function edit(MemorialPagePamphlet $pamphlet): Response
    {
        abort_unless($pamphlet->canBeManagedBy(request()->user()), 403);
        abort_unless(in_array($pamphlet->status, [PamphletStatus::Paid, PamphletStatus::Published], true), 403);

        $pamphlet->load([
            'style',
            'memorialPage.sections' => fn ($query) => $query->orderBy('sort_order'),
            'memorialPage.images',
            'memorialPage.personOfInterest',
        ]);

        $memorialPage = $pamphlet->memorialPage;
        $memorialPage?->setRelation(
            'images',
            $memorialPage->images->sortBy('sort_order')->values()
        );

        return Inertia::render('memorial/Edit', [
            'pamphlet' => $this->pamphletEditPayload($pamphlet),
        ]);
    }

    public function update(UpdateMemorialPageRequest $request, MemorialPagePamphlet $pamphlet): RedirectResponse
    {
        abort_unless($pamphlet->canBeManagedBy(request()->user()), 403);

        $validated = $request->validated();
        $pamphlet->style()->updateOrCreate([], [
            'font_family' => $validated['font_family'] ?? null,
            'is_bold' => $validated['is_bold'],
            'is_italic' => $validated['is_italic'],
            'date_format' => $validated['date_format'],
        ]);

        $memorialPage = $pamphlet->memorialPage()->updateOrCreate([], [
            'title' => $pamphlet->heading,
        ]);

        $removeGalleryImageIds = collect($validated['remove_gallery_image_ids'] ?? [])
            ->filter(static fn (mixed $value): bool => is_string($value) && $value !== '')
            ->unique()
            ->values();

        if ($removeGalleryImageIds->isNotEmpty()) {
            $imagesToDelete = $memorialPage->images()
                ->whereIn('id', $removeGalleryImageIds)
                ->get();

            foreach ($imagesToDelete as $imageToDelete) {
                Storage::disk('public')->delete($imageToDelete->image_path);
                $imageToDelete->delete();
            }
        }

        $removeSectionIds = collect($validated['remove_section_ids'] ?? [])
            ->filter(static fn (mixed $value): bool => is_string($value) && $value !== '')
            ->unique()
            ->values();

        if ($removeSectionIds->isNotEmpty()) {
            $memorialPage->sections()
                ->whereIn('id', $removeSectionIds)
                ->get()
                ->each
                ->delete();
        }

        foreach ($validated['sections'] ?? [] as $index => $section) {
            $sectionId = $section['id'] ?? null;
            $attributes = [
                'title' => $section['title'],
                'body' => $section['body'] ?? '',
                'sort_order' => $index,
            ];

            if ($sectionId !== null && $sectionId !== '') {
                $memorialPage->sections()
                    ->whereKey($sectionId)
                    ->first()
                    ?->update($attributes);

                continue;
            }

            $memorialPage->sections()->create($attributes);
        }

        $existingSortOrder = (int) $memorialPage->images()->max('sort_order');
        foreach ($request->file('gallery_images', []) as $offset => $uploadedImage) {
            $path = $uploadedImage->store('memorial/gallery', 'public');

            $memorialPage->images()->create([
                'image_path' => $path,
                'caption' => null,
                'sort_order' => $existingSortOrder + $offset + 1,
            ]);
        }

        $pamphlet->update(['status' => PamphletStatus::Published]);
        $memorialPage->update([
            'status' => MemorialPageStatus::Published,
            'published_at' => now(),
        ]);

        return redirect()->route('memorial.public.show', $pamphlet->public_slug);
    }

    public function find(): Response
    {
        $search = trim((string) request()->query('search', ''));

        $memorialPages = MemorialPage::query()
            ->with('personOfInterest')
            ->whereHas('pamphlet', function ($query): void {
                $query->whereIn('status', [PamphletStatus::Paid, PamphletStatus::Published]);
            })
            ->when($search !== '', function ($query) use ($search): void {
                $query->whereHas('personOfInterest', function ($personQuery) use ($search): void {
                    $personQuery->whereRaw(
                        "concat_ws(' ', first_name, last_name) ilike ?",
                        ['%'.$search.'%'],
                    )->orWhere('display_name', 'ilike', '%'.$search.'%');
                });
            })
            ->latest('published_at')
            ->paginate(12)
            ->withQueryString()
            ->through(fn (MemorialPage $memorialPage): array => [
                'id' => $memorialPage->id,
                'title' => $memorialPage->title,
                'person_full_name' => $memorialPage->personOfInterest?->display_name,
                'date_of_birth' => $memorialPage->personOfInterest?->date_of_birth?->toDateString(),
                'date_of_passing' => $memorialPage->personOfInterest?->date_of_passing?->toDateString(),
                'url' => route('memorial.public.show', $memorialPage->public_slug),
            ]);

        return Inertia::render('memorial/Find', [
            'memorialPages' => $memorialPages,
            'search' => $search,
        ]);
    }

    public function show(string $slug): Response
    {
        $memorialPage = MemorialPage::query()
            ->with([
                'pamphlet.style',
                'sections' => fn ($query) => $query->orderBy('sort_order'),
                'images',
                'personOfInterest',
            ])
            ->where('public_slug', $slug)
            ->whereHas('pamphlet', function ($query): void {
                $query->whereIn('status', [PamphletStatus::Paid, PamphletStatus::Published]);
            })
            ->firstOrFail();

        $pamphlet = $memorialPage->pamphlet;

        abort_if($pamphlet === null, 404);

        $memorialPage->setRelation(
            'images',
            $memorialPage->images->sortBy('sort_order')->values()
        );

        return Inertia::render('memorial/PublicShow', [
            'pamphlet' => $this->publicShowPayload($pamphlet, $memorialPage),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function pamphletEditPayload(MemorialPagePamphlet $pamphlet): array
    {
        $memorialPage = $pamphlet->memorialPage;
        $personOfInterest = $memorialPage?->personOfInterest;

        return [
            'id' => $pamphlet->id,
            'heading' => $pamphlet->heading,
            'person_full_name' => $pamphlet->person_full_name,
            'status' => $pamphlet->status?->value ?? $pamphlet->status,
            'pamphlet_style' => $pamphlet->style,
            'pamphlet_qr_code' => $personOfInterest ? [
                'target_url' => route('memorial.public.show', $memorialPage->public_slug),
                'image_path' => $personOfInterest->qr_code_path,
            ] : null,
            'memorial_page' => $memorialPage ? [
                'gallery_images' => $memorialPage->images,
                'sections' => $memorialPage->sections->map(fn ($section): array => [
                    'id' => $section->id,
                    'title' => $section->title,
                    'body' => $section->body,
                    'sort_order' => $section->sort_order,
                ]),
            ] : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function publicShowPayload(MemorialPagePamphlet $pamphlet, MemorialPage $memorialPage): array
    {
        $flowerMessages = $memorialPage->messages()
            ->with('authorUser')
            ->where('context', 'flowers')
            ->where('status', 'approved')
            ->whereHas('transaction', function ($query): void {
                $query->where('status', TransactionStatus::Complete);
            })
            ->orderByDesc('created_at')
            ->get();

        $memorialSites = $memorialPage->personOfInterest !== null
            ? MemorialSite::query()
                ->where('person_of_interest_id', $memorialPage->personOfInterest->id)
                ->get()
            : collect();

        $isActiveDay = $memorialPage->active_day_date !== null
            && $memorialPage->active_day_date->isToday();

        return [
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
            'public_slug' => $memorialPage->public_slug,
            'pamphlet_style' => $pamphlet->style,
            'pamphlet_qr_code' => $memorialPage->personOfInterest ? [
                'target_url' => route('memorial.public.show', $memorialPage->public_slug),
                'image_path' => $memorialPage->personOfInterest->qr_code_path,
            ] : null,
            'memorial_page' => [
                'gallery_enabled' => $memorialPage->gallery_enabled,
                'gallery_images' => $memorialPage->images,
                'sections' => $memorialPage->sections
                    ->where('is_visible', true)
                    ->values()
                    ->map(fn ($section): array => [
                        'id' => $section->id,
                        'title' => $section->title,
                        'body' => $section->body,
                        'sort_order' => $section->sort_order,
                    ]),
            ],
            'flower_messages' => $flowerMessages->map(fn (MemorialPageMessage $message): array => [
                'id' => $message->id,
                'author_name' => $message->authorUser?->name,
                'body' => $message->body,
                'created_at' => $message->created_at?->toIso8601String(),
            ]),
            'memorial_sites' => $memorialSites->map(fn (MemorialSite $site): array => [
                'latitude' => (float) $site->latitude,
                'longitude' => (float) $site->longitude,
                'geofence_radius_m' => $site->geofence_radius_m,
            ]),
            'active_day' => [
                'is_active_day' => $isActiveDay,
                'live_comments_enabled' => $memorialPage->live_comments_enabled,
                'live_url' => route('memorial.live.show', $memorialPage->public_slug),
            ],
            'flowers_store_url' => route('memorial.flowers.store', $memorialPage->public_slug),
            'login_url' => route('login'),
            'register_url' => route('register', ['intent' => 'live']),
        ];
    }
}
