<?php

use App\Services\BackgroundRecommendationService;

it('recommends kids for age twelve and below', function () {
    $service = new BackgroundRecommendationService;

    expect($service->recommendedCollectionSlug(now()->subYears(10)->toDateString()))
        ->toBe('kids');
});

it('recommends teens for age thirteen to eighteen', function () {
    $service = new BackgroundRecommendationService;

    expect($service->recommendedCollectionSlug(now()->subYears(16)->toDateString()))
        ->toBe('teens');
});

it('recommends adults for age above eighteen', function () {
    $service = new BackgroundRecommendationService;

    expect($service->recommendedCollectionSlug(now()->subYears(30)->toDateString()))
        ->toBe('adults');
});
