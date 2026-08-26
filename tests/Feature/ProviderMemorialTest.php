<?php

use App\Enums\PamphletStatus;
use App\Enums\ServiceProviderRole;
use App\Mail\FamilyMemorialReadyMail;
use App\Models\MemorialPagePamphlet;
use App\Models\MemorialPagePamphletBackground;
use App\Models\MemorialPagePamphletBackgroundCollection;
use App\Models\ServiceProvider;
use App\Models\ServiceProviderBackground;
use App\Models\ServiceProviderUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function memorialProviderSetup(int $credits = 2): array
{
    $serviceProvider = ServiceProvider::factory()->active()->withCredits($credits)->create();
    $owner = User::factory()->create();
    ServiceProviderUser::factory()->owner()->create([
        'service_provider_id' => $serviceProvider->id,
        'user_id' => $owner->id,
    ]);

    $collection = MemorialPagePamphletBackgroundCollection::factory()->create();
    $background = MemorialPagePamphletBackground::factory()->create([
        'collection_id' => $collection->id,
        'is_active' => true,
    ]);

    return [$serviceProvider, $owner, $background];
}

/**
 * @return array<string, mixed>
 */
function memorialPayload(MemorialPagePamphletBackground $background, array $overrides = []): array
{
    return array_merge([
        'heading' => 'In loving memory',
        'person_full_name' => 'John Doe',
        'family_contact_name' => 'Mary Doe',
        'family_contact_email' => 'mary@family.test',
        'date_of_birth' => '1950-01-01',
        'date_of_passing' => '2024-06-01',
        'date_format' => 'd M Y',
        'image_shape' => 'square',
        'image_crop_mode' => 'cover',
        'short_text' => 'Forever in our hearts.',
        'background_source' => 'catalogue',
        'background_id' => $background->id,
        'image' => UploadedFile::fake()->image('portrait.jpg'),
        'heading_color' => '#000000',
        'name_color' => '#000000',
        'short_text_color' => '#000000',
        'dates_color' => '#000000',
    ], $overrides);
}

test('creating a memorial consumes one credit and marks the pamphlet paid', function () {
    Mail::fake();
    Storage::fake('public');
    [$serviceProvider, $owner, $background] = memorialProviderSetup(2);

    $this->actingAs($owner)
        ->post(route('provider.memorials.store'), memorialPayload($background))
        ->assertRedirect();

    expect($serviceProvider->fresh()->credits_remaining)->toBe(1);

    $pamphlet = MemorialPagePamphlet::query()->latest()->first();

    expect($pamphlet->status)->toBe(PamphletStatus::Paid)
        ->and($pamphlet->memorialPage?->personOfInterest?->service_provider_id)->toBe($serviceProvider->id);

    $family = User::query()->where('email', 'mary@family.test')->first();

    expect($family)->not->toBeNull();
    expect($pamphlet->memorialPage?->personOfInterest?->users()
        ->where('users.id', $family->id)
        ->wherePivot('role', 'owner')
        ->exists())->toBeTrue();

    Mail::assertSent(FamilyMemorialReadyMail::class);
});

test('providers cannot create memorials without credits', function () {
    Storage::fake('public');
    [$serviceProvider, $owner, $background] = memorialProviderSetup(0);

    $this->actingAs($owner)
        ->post(route('provider.memorials.store'), memorialPayload($background))
        ->assertSessionHasErrors('credits');

    expect(MemorialPagePamphlet::query()->count())->toBe(0)
        ->and($serviceProvider->fresh()->credits_remaining)->toBe(0);
});

test('deleting a memorial does not restore a credit', function () {
    Mail::fake();
    Storage::fake('public');
    [$serviceProvider, $owner, $background] = memorialProviderSetup(1);

    $this->actingAs($owner)
        ->post(route('provider.memorials.store'), memorialPayload($background))
        ->assertRedirect();

    $pamphlet = MemorialPagePamphlet::query()->latest()->first();

    $this->actingAs($owner)
        ->delete(route('provider.memorials.destroy', $pamphlet))
        ->assertRedirect(route('provider.memorials.index'));

    expect($serviceProvider->fresh()->credits_remaining)->toBe(0);
});

test('provider members can still edit a client memorial', function () {
    Mail::fake();
    Storage::fake('public');
    [$serviceProvider, $owner, $background] = memorialProviderSetup(1);

    $this->actingAs($owner)
        ->post(route('provider.memorials.store'), memorialPayload($background))
        ->assertRedirect();

    $pamphlet = MemorialPagePamphlet::query()->latest()->first();

    $this->actingAs($owner)
        ->get(route('memorial.edit', $pamphlet))
        ->assertOk();
});

test('providers can create a memorial with a custom background', function () {
    Mail::fake();
    Storage::fake('public');
    [$serviceProvider, $owner, $background] = memorialProviderSetup(1);

    $providerBackground = ServiceProviderBackground::factory()->create([
        'service_provider_id' => $serviceProvider->id,
    ]);

    $this->actingAs($owner)
        ->post(route('provider.memorials.store'), memorialPayload($background, [
            'background_source' => 'provider',
            'background_id' => null,
            'service_provider_background_id' => $providerBackground->id,
            'family_contact_email' => 'other@family.test',
        ]))
        ->assertRedirect();

    $pamphlet = MemorialPagePamphlet::query()->latest()->first();

    expect($pamphlet->service_provider_background_id)->toBe($providerBackground->id)
        ->and($pamphlet->background_id)->toBeNull();
});

test('staff members can create memorials when the parlour is active', function () {
    Mail::fake();
    Storage::fake('public');
    [$serviceProvider, $owner, $background] = memorialProviderSetup(1);
    $staff = User::factory()->create();
    ServiceProviderUser::factory()->create([
        'service_provider_id' => $serviceProvider->id,
        'user_id' => $staff->id,
        'role' => ServiceProviderRole::Staff,
    ]);

    $this->actingAs($staff)
        ->post(route('provider.memorials.store'), memorialPayload($background, [
            'family_contact_email' => 'staff-created@family.test',
        ]))
        ->assertRedirect();

    expect($serviceProvider->fresh()->credits_remaining)->toBe(0);
});
