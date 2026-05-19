<?php

namespace App\Services;

use App\Models\Background;
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
     * @return Collection<int, Background>
     */
    public function backgroundsWithRecommendation(string $dateOfBirth): Collection
    {
        $recommendedSlug = $this->recommendedCollectionSlug($dateOfBirth);

        return Background::query()
            ->with('backgroundCollection')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function (Background $background) use ($recommendedSlug): Background {
                $background->setAttribute(
                    'is_recommended',
                    $background->backgroundCollection?->slug === $recommendedSlug
                );

                return $background;
            });
    }
}
