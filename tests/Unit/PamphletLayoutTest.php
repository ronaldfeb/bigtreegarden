<?php

use App\Support\PamphletLayout;

it('returns the default stacked layout', function () {
    $defaults = PamphletLayout::defaults();

    expect($defaults)->toHaveKeys(['heading', 'name', 'dates', 'tribute', 'photo']);
    expect($defaults['heading'])->toHaveKeys(['x', 'y', 'w', 'fontSize']);
    expect($defaults['photo'])->toHaveKeys(['x', 'y', 'w', 'h']);
});

it('normalizes null layout to defaults', function () {
    expect(PamphletLayout::normalize(null))->toBe(PamphletLayout::defaults());
});

it('clamps out of range values when normalizing', function () {
    $normalized = PamphletLayout::normalize([
        'heading' => ['x' => -10, 'y' => 200, 'w' => 5, 'fontSize' => 50],
        'photo' => ['x' => 10, 'y' => 10, 'w' => 200, 'h' => 5],
    ]);

    expect($normalized['heading']['x'])->toBe(0.0);
    expect($normalized['heading']['y'])->toBe(95.0);
    expect($normalized['heading']['w'])->toBe(10.0);
    expect($normalized['heading']['fontSize'])->toBe(14.0);
    expect($normalized['photo']['w'])->toBe(90.0);
    expect($normalized['photo']['h'])->toBe(10.0);
});

it('decodes a json layout string', function () {
    $decoded = PamphletLayout::decode(json_encode(PamphletLayout::defaults()));

    expect($decoded)->toBeArray()->toHaveKey('heading');
});

it('returns null for invalid json', function () {
    expect(PamphletLayout::decode('{not-json'))->toBeNull();
});
