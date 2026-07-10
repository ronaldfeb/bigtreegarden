<?php

use App\Models\Ambassador;
use App\Models\AmbassadorImage;
use App\Models\Testimonial;
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
        ->has('testimonials')
        ->has('partners')
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
        ->has('testimonials')
        ->has('partners')
    );
});

it('includes featured published testimonials on the landing page', function () {
    $featured = Testimonial::factory()->featured()->create([
        'name' => 'Featured Family',
        'body' => 'A wonderful tribute.',
    ]);
    Testimonial::factory()->draft()->featured()->create(['name' => 'Draft Testimonial']);
    Testimonial::factory()->create(['name' => 'Not Featured', 'is_featured' => false]);

    $response = $this->get('/');

    $response->assertOk();

    $response->assertInertia(fn (Assert $page) => $page
        ->has('testimonials', 1)
        ->where('testimonials.0.id', $featured->id)
        ->where('testimonials.0.name', 'Featured Family')
        ->where('testimonials.0.body', 'A wonderful tribute.')
    );
});
