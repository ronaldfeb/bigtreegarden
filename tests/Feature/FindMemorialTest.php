<?php

use App\Enums\PamphletStatus;
use App\Models\MemorialPagePamphlet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

function createFindableMemorial(string $firstName, string $lastName, PamphletStatus $status = PamphletStatus::Published): MemorialPagePamphlet
{
    $pamphlet = MemorialPagePamphlet::factory()->create(['status' => $status]);

    $pamphlet->memorialPage->personOfInterest->update([
        'first_name' => $firstName,
        'last_name' => $lastName,
        'display_name' => "{$firstName} {$lastName}",
    ]);

    return $pamphlet;
}

it('renders the find a memorial page with published memorials only', function () {
    createFindableMemorial('James', 'Morrison');
    createFindableMemorial('Draft', 'Person', PamphletStatus::Draft);

    $this->get(route('memorial.find'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('memorial/Find')
            ->has('memorialPages.data', 1)
            ->where('memorialPages.data.0.person_full_name', 'James Morrison'));
});

it('filters memorials by name search', function () {
    createFindableMemorial('James', 'Morrison');
    createFindableMemorial('Elizabeth', 'Sithole');

    $this->get(route('memorial.find', ['search' => 'sithole']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('memorial/Find')
            ->has('memorialPages.data', 1)
            ->where('memorialPages.data.0.person_full_name', 'Elizabeth Sithole')
            ->where('search', 'sithole'));
});

it('returns an empty result set for unmatched searches', function () {
    createFindableMemorial('James', 'Morrison');

    $this->get(route('memorial.find', ['search' => 'nonexistent']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('memorial/Find')
            ->has('memorialPages.data', 0));
});

it('links each result to the public memorial page', function () {
    $pamphlet = createFindableMemorial('James', 'Morrison');
    $slug = $pamphlet->memorialPage->public_slug;

    $this->get(route('memorial.find'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('memorialPages.data.0.url', route('memorial.public.show', $slug)));
});
