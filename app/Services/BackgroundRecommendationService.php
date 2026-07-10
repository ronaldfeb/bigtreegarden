<?php

namespace App\Services;

use App\Models\MemorialPagePamphletBackground;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class BackgroundRecommendationService
{
    public function recommendedCollectionSlug(string $dateOfBirth): string
    {
        $age = Carbon::parse($dateOfBirth)->age;

        return match (true) {
            $age <= 12 => 'kids',
            $age <= 18 => 'teens',
            default => 'adults',
        };
    }

    /**
     * @return Collection<int, MemorialPagePamphletBackground>
     */
    public function backgroundsWithRecommendation(string $dateOfBirth): Collection
    {
        $recommendedSlug = $this->recommendedCollectionSlug($dateOfBirth);

        return MemorialPagePamphletBackground::query()
            ->with('collection')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function (MemorialPagePamphletBackground $background) use ($recommendedSlug): MemorialPagePamphletBackground {
                $background->setAttribute(
                    'is_recommended',
                    $background->collection?->slug === $recommendedSlug
                );

                return $background;
            });
    }
}
