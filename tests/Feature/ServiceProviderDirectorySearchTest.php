<?php

use App\Models\ServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('directory can be searched by name city and province', function () {
    ServiceProvider::factory()->active()->create([
        'name' => 'Cape Memorials',
        'city' => 'Cape Town',
        'province' => 'Western Cape',
    ]);
    ServiceProvider::factory()->active()->create([
        'name' => 'Joburg Care',
        'city' => 'Johannesburg',
        'province' => 'Gauteng',
    ]);
    ServiceProvider::factory()->create([
        'name' => 'Pending Cape',
        'city' => 'Cape Town',
        'province' => 'Western Cape',
        'status' => 'pending',
    ]);

    $this->get(route('providers.index', ['name' => 'Cape', 'province' => 'Western Cape']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('marketing/Providers/Index')
            ->has('providers.data', 1)
            ->where('providers.data.0.name', 'Cape Memorials')
            ->where('filters.name', 'Cape')
            ->where('filters.province', 'Western Cape'),
        );

    $this->get(route('providers.index', ['city' => 'Johannesburg']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('providers.data', 1)
            ->where('providers.data.0.name', 'Joburg Care'),
        );
});

test('suspended providers are excluded from the directory', function () {
    ServiceProvider::factory()->create([
        'name' => 'Suspended Care',
        'status' => 'suspended',
    ]);

    $this->get(route('providers.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->has('providers.data', 0));
});
