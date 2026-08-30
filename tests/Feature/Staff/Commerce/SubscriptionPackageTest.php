<?php

use App\Models\SubscriptionPackage;
use App\Models\SubscriptionPackageFeature;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('includes features on the edit subscription package page', function () {
    $package = SubscriptionPackage::factory()->create(['name' => 'Funeral Memorial']);
    $feature = SubscriptionPackageFeature::factory()->create([
        'subscription_package_id' => $package->id,
        'label' => 'Printable pamphlet',
        'sort_order' => 0,
    ]);

    $this->actingAs(makeStaffUser())
        ->get(route('staff.commerce.subscription-packages.edit', $package))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('staff/commerce/subscription-packages/Edit')
            ->has('package.features', 1)
            ->where('package.features.0.id', $feature->id)
            ->where('package.features.0.label', 'Printable pamphlet'));
});

it('syncs subscription package features on update', function () {
    $package = SubscriptionPackage::factory()->create(['name' => 'Funeral Memorial']);
    $keep = SubscriptionPackageFeature::factory()->create([
        'subscription_package_id' => $package->id,
        'label' => 'Personalised memorial page',
        'sort_order' => 0,
    ]);
    $remove = SubscriptionPackageFeature::factory()->create([
        'subscription_package_id' => $package->id,
        'label' => 'Old benefit',
        'sort_order' => 1,
    ]);

    $this->actingAs(makeStaffUser())
        ->put(route('staff.commerce.subscription-packages.update', $package), [
            'name' => 'Funeral Memorial',
            'description' => $package->description,
            'price_cents' => $package->price_cents,
            'currency' => $package->currency,
            'billing_interval' => $package->billing_interval,
            'is_featured' => $package->is_featured,
            'is_active' => $package->is_active,
            'sort_order' => $package->sort_order,
            'sync_features' => '1',
            'features' => [
                [
                    'id' => $keep->id,
                    'label' => 'Personalised memorial page',
                    'description' => 'A lasting online tribute',
                    'is_included' => '1',
                ],
                [
                    'label' => 'Guest tributes',
                    'description' => '',
                    'is_included' => '0',
                ],
            ],
        ])
        ->assertRedirect(route('staff.commerce.subscription-packages.show', $package));

    $package->refresh()->load('features');

    expect($package->features)->toHaveCount(2)
        ->and($package->features->firstWhere('id', $keep->id)?->description)->toBe('A lasting online tribute')
        ->and($package->features->firstWhere('label', 'Guest tributes')?->is_included)->toBeFalse();

    expect(SubscriptionPackageFeature::query()->find($remove->id))->toBeNull();
    $this->assertSoftDeleted($remove);
});

it('rejects features that belong to another package', function () {
    $package = SubscriptionPackage::factory()->create();
    $otherFeature = SubscriptionPackageFeature::factory()->create();

    $this->actingAs(makeStaffUser())
        ->put(route('staff.commerce.subscription-packages.update', $package), [
            'name' => $package->name,
            'price_cents' => $package->price_cents,
            'billing_interval' => $package->billing_interval,
            'sync_features' => '1',
            'features' => [
                [
                    'id' => $otherFeature->id,
                    'label' => 'Stolen feature',
                    'is_included' => '1',
                ],
            ],
        ])
        ->assertSessionHasErrors('features.0.id');
});

it('clears all features when the synced list is empty', function () {
    $package = SubscriptionPackage::factory()->create();
    SubscriptionPackageFeature::factory()->create([
        'subscription_package_id' => $package->id,
    ]);

    $this->actingAs(makeStaffUser())
        ->put(route('staff.commerce.subscription-packages.update', $package), [
            'name' => $package->name,
            'price_cents' => $package->price_cents,
            'billing_interval' => $package->billing_interval,
            'sync_features' => '1',
        ])
        ->assertRedirect(route('staff.commerce.subscription-packages.show', $package));

    expect($package->fresh()->features()->count())->toBe(0);
});

it('leaves features unchanged when they are not submitted', function () {
    $package = SubscriptionPackage::factory()->create();
    $feature = SubscriptionPackageFeature::factory()->create([
        'subscription_package_id' => $package->id,
        'label' => 'Keep me',
    ]);

    $this->actingAs(makeStaffUser())
        ->put(route('staff.commerce.subscription-packages.update', $package), [
            'name' => 'Updated name',
            'price_cents' => $package->price_cents,
            'billing_interval' => $package->billing_interval,
        ])
        ->assertRedirect(route('staff.commerce.subscription-packages.show', $package));

    expect($package->fresh()->features()->count())->toBe(1)
        ->and($feature->fresh()->label)->toBe('Keep me');
});
