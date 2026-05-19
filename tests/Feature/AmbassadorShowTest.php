<?php

use App\Models\Ambassador;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

it('renders the ambassador detail page for active ambassadors', function () {
    $ambassador = Ambassador::factory()->withSocials()->create([
        'name' => 'Jane Doe',
        'title' => 'Community liaison',
        'description' => 'Helps families plan memorials.',
    ]);

    $response = $this->get(route('ambassadors.show', $ambassador));

    $response->assertOk();

    $response->assertInertia(fn (Assert $page) => $page
        ->component('ambassadors/Show')
        ->has('ambassador')
        ->where('ambassador.id', $ambassador->id)
        ->where('ambassador.name', 'Jane Doe')
        ->where('ambassador.title', 'Community liaison')
        ->where('ambassador.description', 'Helps families plan memorials.')
        ->where('ambassador.image_url', $ambassador->image_url)
        ->where('ambassador.handle_linkedin', $ambassador->handle_linkedin)
        ->where('ambassador.website_url', $ambassador->website_url)
    );
});

it('returns not found for inactive ambassadors', function () {
    $ambassador = Ambassador::factory()->inactive()->create();

    $this->get(route('ambassadors.show', $ambassador))->assertNotFound();
});

it('returns not found for unknown ambassadors', function () {
    $this->get(route('ambassadors.show', '00000000-0000-4000-8000-000000000000'))->assertNotFound();
});
