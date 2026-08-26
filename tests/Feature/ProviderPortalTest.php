<?php

use App\Models\ServiceProvider;
use App\Models\ServiceProviderImage;
use App\Models\ServiceProviderService;
use App\Models\ServiceProviderSocialMedia;
use App\Models\ServiceProviderSpeciality;
use App\Models\ServiceProviderUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function createProviderMember(ServiceProvider $serviceProvider): User
{
    $user = User::factory()->create();

    ServiceProviderUser::factory()->create([
        'service_provider_id' => $serviceProvider->id,
        'user_id' => $user->id,
    ]);

    return $user;
}

test('guests are redirected from the provider portal', function () {
    $this->get(route('provider.dashboard'))
        ->assertRedirect(route('login'));
});

test('authenticated non-members cannot access the provider portal', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('provider.dashboard'))
        ->assertForbidden();
});

test('members can view the provider dashboard', function () {
    $serviceProvider = ServiceProvider::factory()->create();
    ServiceProviderService::factory()->count(2)->create(['service_provider_id' => $serviceProvider->id]);
    $user = createProviderMember($serviceProvider);

    $this->actingAs($user)
        ->get(route('provider.dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('provider/Dashboard')
            ->where('serviceProvider.id', $serviceProvider->id)
            ->where('counts.services', 2)
            ->has('profileCompleteness'));
});

test('members can update their provider profile', function () {
    $serviceProvider = ServiceProvider::factory()->create(['slug' => 'original-slug']);
    $user = createProviderMember($serviceProvider);

    $this->actingAs($user)
        ->patch(route('provider.profile.update'), [
            'name' => 'Updated Funeral Services',
            'email' => 'contact@updated.test',
            'phone' => '011 555 0000',
            'website_url' => 'https://updated.test',
            'physical_address' => '1 Main Road',
            'city' => 'Cape Town',
            'province' => 'Western Cape',
            'registration_number' => '2020/123456/07',
            'description' => 'We provide dignified services.',
            'slug' => 'hacked-slug',
        ])
        ->assertRedirect(route('provider.profile.edit'));

    $serviceProvider->refresh();

    expect($serviceProvider->name)->toBe('Updated Funeral Services')
        ->and($serviceProvider->email)->toBe('contact@updated.test')
        ->and($serviceProvider->city)->toBe('Cape Town')
        ->and($serviceProvider->slug)->toBe('original-slug');
});

test('members can upload a logo and cover image', function () {
    Storage::fake('public');

    $serviceProvider = ServiceProvider::factory()->create();
    $user = createProviderMember($serviceProvider);

    $this->actingAs($user)
        ->patch(route('provider.profile.update'), [
            'name' => $serviceProvider->name,
            'email' => $serviceProvider->email,
            'phone' => $serviceProvider->phone ?? '0115550000',
            'registration_number' => $serviceProvider->registration_number ?? '2020/123456/07',
            'logo' => UploadedFile::fake()->image('logo.png'),
            'cover_image' => UploadedFile::fake()->image('cover.jpg'),
        ])
        ->assertRedirect(route('provider.profile.edit'));

    $serviceProvider->refresh();

    expect($serviceProvider->logo_path)->toStartWith("providers/{$serviceProvider->id}/")
        ->and($serviceProvider->cover_image_path)->toStartWith("providers/{$serviceProvider->id}/");

    Storage::disk('public')->assertExists($serviceProvider->logo_path);
    Storage::disk('public')->assertExists($serviceProvider->cover_image_path);
});

test('members can create, update, and delete services', function () {
    $serviceProvider = ServiceProvider::factory()->create();
    $user = createProviderMember($serviceProvider);

    $this->actingAs($user)
        ->post(route('provider.services.store'), [
            'name' => 'Cremation',
            'description' => 'Full cremation service.',
            'price_from_cents' => 500000,
        ])
        ->assertRedirect(route('provider.services.index'));

    $service = $serviceProvider->services()->first();
    expect($service)->not->toBeNull()
        ->and($service->name)->toBe('Cremation');

    $this->actingAs($user)
        ->patch(route('provider.services.update', $service), [
            'name' => 'Cremation & Memorial',
            'price_from_cents' => 600000,
        ])
        ->assertRedirect(route('provider.services.index'));

    expect($service->refresh()->name)->toBe('Cremation & Memorial');

    $this->actingAs($user)
        ->delete(route('provider.services.destroy', $service))
        ->assertRedirect(route('provider.services.index'));

    expect(ServiceProviderService::query()->find($service->id))->toBeNull();
});

test('members cannot modify services belonging to another provider', function () {
    $serviceProvider = ServiceProvider::factory()->create();
    $user = createProviderMember($serviceProvider);

    $otherService = ServiceProviderService::factory()->create();

    $this->actingAs($user)
        ->patch(route('provider.services.update', $otherService), ['name' => 'Hijacked'])
        ->assertNotFound();

    $this->actingAs($user)
        ->delete(route('provider.services.destroy', $otherService))
        ->assertNotFound();

    expect($otherService->refresh()->name)->not->toBe('Hijacked');
});

test('members can create, update, and delete specialities', function () {
    $serviceProvider = ServiceProvider::factory()->create();
    $user = createProviderMember($serviceProvider);

    $this->actingAs($user)
        ->post(route('provider.specialities.store'), ['name' => 'Repatriation'])
        ->assertRedirect(route('provider.specialities.index'));

    $speciality = $serviceProvider->specialities()->first();
    expect($speciality)->not->toBeNull();

    $this->actingAs($user)
        ->patch(route('provider.specialities.update', $speciality), ['name' => 'International repatriation'])
        ->assertRedirect(route('provider.specialities.index'));

    expect($speciality->refresh()->name)->toBe('International repatriation');

    $this->actingAs($user)
        ->delete(route('provider.specialities.destroy', $speciality))
        ->assertRedirect(route('provider.specialities.index'));

    expect(ServiceProviderSpeciality::query()->find($speciality->id))->toBeNull();
});

test('members cannot modify specialities belonging to another provider', function () {
    $serviceProvider = ServiceProvider::factory()->create();
    $user = createProviderMember($serviceProvider);

    $otherSpeciality = ServiceProviderSpeciality::factory()->create();

    $this->actingAs($user)
        ->delete(route('provider.specialities.destroy', $otherSpeciality))
        ->assertNotFound();
});

test('members can create, update, and delete social media links', function () {
    $serviceProvider = ServiceProvider::factory()->create();
    $user = createProviderMember($serviceProvider);

    $this->actingAs($user)
        ->post(route('provider.social-media.store'), [
            'platform' => 'Facebook',
            'url' => 'https://facebook.com/example',
        ])
        ->assertRedirect(route('provider.social-media.index'));

    $socialMedia = $serviceProvider->socialMedia()->first();
    expect($socialMedia)->not->toBeNull();

    $this->actingAs($user)
        ->patch(route('provider.social-media.update', $socialMedia), [
            'platform' => 'Instagram',
            'url' => 'https://instagram.com/example',
        ])
        ->assertRedirect(route('provider.social-media.index'));

    expect($socialMedia->refresh()->platform)->toBe('Instagram');

    $this->actingAs($user)
        ->delete(route('provider.social-media.destroy', $socialMedia))
        ->assertRedirect(route('provider.social-media.index'));

    expect(ServiceProviderSocialMedia::query()->find($socialMedia->id))->toBeNull();
});

test('members cannot modify social media links belonging to another provider', function () {
    $serviceProvider = ServiceProvider::factory()->create();
    $user = createProviderMember($serviceProvider);

    $otherSocialMedia = ServiceProviderSocialMedia::factory()->create();

    $this->actingAs($user)
        ->delete(route('provider.social-media.destroy', $otherSocialMedia))
        ->assertNotFound();
});

test('members can upload and delete gallery images', function () {
    Storage::fake('public');

    $serviceProvider = ServiceProvider::factory()->create();
    $user = createProviderMember($serviceProvider);

    $this->actingAs($user)
        ->post(route('provider.images.store'), [
            'image' => UploadedFile::fake()->image('gallery.jpg'),
            'caption' => 'Our premises',
        ])
        ->assertRedirect(route('provider.images.index'));

    $image = $serviceProvider->images()->first();

    expect($image)->not->toBeNull()
        ->and($image->caption)->toBe('Our premises')
        ->and($image->image_path)->toStartWith("providers/{$serviceProvider->id}/gallery/");

    Storage::disk('public')->assertExists($image->image_path);

    $this->actingAs($user)
        ->delete(route('provider.images.destroy', $image))
        ->assertRedirect(route('provider.images.index'));

    Storage::disk('public')->assertMissing($image->image_path);
    expect(ServiceProviderImage::query()->find($image->id))->toBeNull();
});

test('members cannot delete gallery images belonging to another provider', function () {
    $serviceProvider = ServiceProvider::factory()->create();
    $user = createProviderMember($serviceProvider);

    $otherImage = ServiceProviderImage::factory()->create();

    $this->actingAs($user)
        ->delete(route('provider.images.destroy', $otherImage))
        ->assertNotFound();
});
