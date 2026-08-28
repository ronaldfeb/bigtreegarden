<?php

use App\Models\Policy;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('staff can update policy content without unpublishing the public page', function () {
    $staff = makeStaffUser();
    $policy = Policy::factory()->create([
        'type' => config('constants.policy.type.privacy_policy'),
        'title' => 'Privacy Policy',
        'body' => '<p>Old content</p>',
        'published_at' => now()->subDay(),
    ]);

    $this->actingAs($staff)
        ->patch(route('staff.content.policies.update', $policy), [
            'title' => 'Updated Privacy Policy',
            'body' => '<p>Updated content</p>',
            'version' => 'v2.0',
            'published_at' => '',
        ])
        ->assertRedirect(route('staff.content.policies.show', $policy));

    $policy->refresh();

    expect($policy->type)->toBe(config('constants.policy.type.privacy_policy'))
        ->and($policy->title)->toBe('Updated Privacy Policy')
        ->and($policy->body)->toBe('<p>Updated content</p>')
        ->and($policy->published_at)->not->toBeNull();

    $this->get(route('policies.privacy'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('marketing/Policy/Show')
            ->where('policy.title', 'Updated Privacy Policy'));
});
