<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateMemorialPageRequest;
use App\Models\Pamphlet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class MemorialPageController extends Controller
{
    public function edit(Pamphlet $pamphlet): Response
    {
        abort_if($pamphlet->user_id !== request()->user()->id, 403);
        abort_unless($pamphlet->status === 'paid' || $pamphlet->status === 'published', 403);

        $pamphlet->load([
            'pamphletStyle',
            'memorialPage.additionalSections',
            'memorialPage.galleryImages',
            'pamphletQrCode',
        ]);

        $pamphlet->memorialPage?->setRelation(
            'galleryImages',
            $pamphlet->memorialPage->galleryImages->sortBy('sort_order')->values()
        );

        return Inertia::render('memorial/Edit', [
            'pamphlet' => $pamphlet,
        ]);
    }

    public function update(UpdateMemorialPageRequest $request, Pamphlet $pamphlet): RedirectResponse
    {
        abort_if($pamphlet->user_id !== request()->user()->id, 403);

        $validated = $request->validated();
        $pamphlet->pamphletStyle()->updateOrCreate([], [
            'font_family' => $validated['font_family'] ?? null,
            'is_bold' => $validated['is_bold'],
            'is_italic' => $validated['is_italic'],
            'date_format' => $validated['date_format'],
        ]);

        $memorialPage = $pamphlet->memorialPage()->updateOrCreate([], [
            'funeral_programme' => $validated['funeral_programme'] ?? null,
            'obituary' => $validated['obituary'] ?? null,
            'hymns' => $validated['hymns'] ?? null,
        ]);

        $removeGalleryImageIds = collect($validated['remove_gallery_image_ids'] ?? [])
            ->filter(static fn (mixed $value): bool => is_string($value) && $value !== '')
            ->unique()
            ->values();

        if ($removeGalleryImageIds->isNotEmpty()) {
            $imagesToDelete = $memorialPage->galleryImages()
                ->whereIn('id', $removeGalleryImageIds)
                ->get();

            foreach ($imagesToDelete as $imageToDelete) {
                Storage::disk('public')->delete($imageToDelete->image_path);
                $imageToDelete->delete();
            }
        }

        $memorialPage->additionalSections()->delete();
        foreach ($validated['additional_sections'] ?? [] as $index => $section) {
            $memorialPage->additionalSections()->create([
                'title' => $section['title'],
                'content' => $section['content'] ?? null,
                'sort_order' => $index,
            ]);
        }

        $existingSortOrder = (int) $memorialPage->galleryImages()->max('sort_order');
        foreach ($request->file('gallery_images', []) as $offset => $uploadedImage) {
            $path = $uploadedImage->store('memorial/gallery', 'public');

            $memorialPage->galleryImages()->create([
                'image_path' => $path,
                'caption' => null,
                'sort_order' => $existingSortOrder + $offset + 1,
            ]);
        }

        $pamphlet->update(['status' => 'published']);

        return redirect()->route('memorial.public.show', $pamphlet->public_slug);
    }

    public function show(string $slug): Response
    {
        $pamphlet = Pamphlet::query()
            ->with([
                'pamphletStyle',
                'memorialPage.additionalSections',
                'memorialPage.galleryImages',
                'pamphletQrCode',
            ])
            ->where('public_slug', $slug)
            ->whereIn('status', ['paid', 'published'])
            ->firstOrFail();

        $pamphlet->memorialPage?->setRelation(
            'galleryImages',
            $pamphlet->memorialPage->galleryImages->sortBy('sort_order')->values()
        );

        return Inertia::render('memorial/PublicShow', [
            'pamphlet' => $pamphlet,
        ]);
    }
}
