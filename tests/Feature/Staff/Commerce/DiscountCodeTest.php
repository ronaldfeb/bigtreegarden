<?php

use App\Enums\DiscountType;
use App\Models\DiscountCode;
use App\Models\SubscriptionPackage;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lists discount codes for staff', function () {
    $code = DiscountCode::factory()->create([
        'code' => '0AYO1B88',
        'discount_type' => DiscountType::Percent,
        'percent' => 10,
    ]);

    $this->actingAs(makeStaffUser())
        ->get(route('staff.commerce.discount-codes.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('staff/commerce/discount-codes/Index')
            ->has('codes.data', 1)
            ->where('codes.data.0.formatted_code', '0A-YO1B-88')
            ->where('codes.data.0.id', $code->id));
});

it('generates an 8 character alphanumeric code on create', function () {
    $package = SubscriptionPackage::factory()->create();

    $this->actingAs(makeStaffUser())
        ->post(route('staff.commerce.discount-codes.store'), [
            'discount_type' => 'percent',
            'percent' => 15,
            'starts_at' => now()->subHour()->toDateTimeString(),
            'ends_at' => now()->addWeek()->toDateTimeString(),
            'applies_to_all' => '0',
            'target' => SubscriptionPackage::class.':'.$package->id,
        ])
        ->assertRedirect();

    $code = DiscountCode::query()->first();

    expect($code)->not->toBeNull()
        ->and($code->code)->toMatch('/^[A-Z0-9]{8}$/')
        ->and($code->formattedCode())->toMatch('/^[A-Z0-9]{2}-[A-Z0-9]{4}-[A-Z0-9]{2}$/')
        ->and($code->discount_type)->toBe(DiscountType::Percent)
        ->and($code->percent)->toBe(15)
        ->and($code->applies_to_all)->toBeFalse()
        ->and($code->discountable_id)->toBe($package->id);
});

it('creates an all-packages fixed discount code', function () {
    $this->actingAs(makeStaffUser())
        ->post(route('staff.commerce.discount-codes.store'), [
            'discount_type' => 'fixed',
            'amount_cents' => 5000,
            'starts_at' => now()->toDateTimeString(),
            'ends_at' => now()->addDays(3)->toDateTimeString(),
            'applies_to_all' => '1',
        ])
        ->assertRedirect();

    $code = DiscountCode::query()->first();

    expect($code)->not->toBeNull()
        ->and($code->discount_type)->toBe(DiscountType::Fixed)
        ->and($code->amount_cents)->toBe(5000)
        ->and($code->applies_to_all)->toBeTrue()
        ->and($code->discountable_type)->toBeNull();
});

it('validates percent, dates, and target', function () {
    $this->actingAs(makeStaffUser())
        ->post(route('staff.commerce.discount-codes.store'), [
            'discount_type' => 'percent',
            'percent' => 150,
            'starts_at' => now()->addDay()->toDateTimeString(),
            'ends_at' => now()->toDateTimeString(),
            'applies_to_all' => '0',
        ])
        ->assertSessionHasErrors(['percent', 'ends_at', 'discountable_type', 'discountable_id']);
});

it('shows a discount code with formatted code', function () {
    $code = DiscountCode::factory()->create(['code' => 'AB12CD34']);

    $this->actingAs(makeStaffUser())
        ->get(route('staff.commerce.discount-codes.show', $code))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('staff/commerce/discount-codes/Show')
            ->where('code.formatted_code', 'AB-12CD-34')
            ->where('code.status', 'active'));
});

it('voids an unused discount code', function () {
    $code = DiscountCode::factory()->create();

    $this->actingAs(makeStaffUser())
        ->delete(route('staff.commerce.discount-codes.destroy', $code))
        ->assertRedirect(route('staff.commerce.discount-codes.index'));

    $this->assertSoftDeleted($code);
});

it('does not void a used discount code', function () {
    $code = DiscountCode::factory()->used()->create();

    $this->actingAs(makeStaffUser())
        ->delete(route('staff.commerce.discount-codes.destroy', $code))
        ->assertStatus(422);

    expect($code->fresh())->not->toBeNull()
        ->and($code->fresh()->trashed())->toBeFalse();
});

it('only offers active packages when creating discount codes', function () {
    SubscriptionPackage::factory()->create([
        'slug' => 'legacy-package',
        'name' => 'Legacy Package',
        'is_active' => false,
    ]);

    $this->actingAs(makeStaffUser())
        ->get(route('staff.commerce.discount-codes.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('staff/commerce/discount-codes/Create')
            ->where('targets', fn ($targets) => collect($targets)
                ->flatMap(fn (array $group) => $group['options'])
                ->every(fn (array $option) => ! str_contains($option['label'], 'Legacy Package'))));
});

it('stores staff datetime-local values using the business timezone', function () {
    $this->actingAs(makeStaffUser())
        ->post(route('staff.commerce.discount-codes.store'), [
            'discount_type' => 'percent',
            'percent' => 10,
            'starts_at' => now('Africa/Johannesburg')->subMinute()->format('Y-m-d\TH:i'),
            'ends_at' => now('Africa/Johannesburg')->addWeek()->format('Y-m-d\TH:i'),
            'applies_to_all' => '1',
        ])
        ->assertRedirect();

    $code = DiscountCode::query()->firstOrFail();

    expect($code->isUsable())->toBeTrue()
        ->and($code->starts_at->lte(now()))->toBeTrue();
});
