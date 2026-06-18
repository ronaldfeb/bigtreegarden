<?php

use App\Models\Ambassador;
use App\Models\AmbassadorImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

it('renders the marketing landing page', function () {
    $response = $this->get('/');

    $response->assertOk();

    $response->assertInertia(fn (Assert $page) => $page
        ->component('marketing/Landing')
        ->has('canRegister')
        ->has('ambassadors')
        ->where('canRegister', fn ($canRegister) => is_bool($canRegister),
        ),
    );
});

it('includes only active ambassadors on the landing page', function () {
    $active = Ambassador::factory()->create(['name' => 'Active Ambassador']);
    AmbassadorImage::factory()->create([
        'ambassador_id' => $active->id,
        'sort_order' => 0,
    ]);
    Ambassador::factory()->inactive()->create(['name' => 'Inactive Ambassador']);

    $response = $this->get('/');

    $response->assertOk();

    $response->assertInertia(fn (Assert $page) => $page
        ->has('ambassadors', 1)
        ->where('ambassadors.0.id', $active->id)
        ->where('ambassadors.0.name', 'Active Ambassador')
        ->has('ambassadors.0.images', 1)
    );
});
