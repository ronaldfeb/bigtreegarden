<?php

use App\Models\Background;
use App\Models\BackgroundCollection;
use App\Models\Pamphlet;
use App\Models\Payment;
use App\Models\User;
use App\Services\GuestPamphletDraftService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('allows guests to create a draft and view preview before auth', function () {
    Storage::fake('public');
    config()->set('memorial.bypass_payment_for_publish', true);

    $collection = BackgroundCollection::factory()->create(['slug' => 'adults']);
    $background = Background::factory()->create([
        'background_collection_id' => $collection->id,
    ]);

    $response = $this->post('/pamphlets', [
        'heading' => 'Celebrating Life',
        'person_full_name' => 'John Doe',
        'date_of_birth' => '1970-01-01',
        'date_of_passing' => '2024-01-01',
        'date_format' => 'd M Y',
        'image_shape' => 'square',
        'image_crop_mode' => 'cover',
        'short_text' => 'Beloved father and friend.',
        'background_id' => $background->id,
        'image' => UploadedFile::fake()->image('memorial.jpg'),
    ]);

    $pamphlet = Pamphlet::query()->first();

    expect($pamphlet)->not->toBeNull();
    expect($pamphlet->status)->toBe('published');
    expect($pamphlet->user_id)->toBeNull();
    expect($pamphlet->guest_token_hash)->not->toBeNull();

    $response->assertRedirect(route('pamphlets.show', $pamphlet));
    $response->assertCookie('guest_pamphlet_token');
});

it('creates draft status when payment bypass is disabled', function () {
    Storage::fake('public');
    config()->set('memorial.bypass_payment_for_publish', false);

    $collection = BackgroundCollection::factory()->create(['slug' => 'adults']);
    $background = Background::factory()->create([
        'background_collection_id' => $collection->id,
    ]);

    $this->post('/pamphlets', [
        'heading' => 'Draft Mode',
        'person_full_name' => 'Jane Doe',
        'date_of_birth' => '1980-01-01',
        'date_of_passing' => '2024-01-01',
        'date_format' => 'd M Y',
        'image_shape' => 'square',
        'image_crop_mode' => 'cover',
        'short_text' => 'Draft state test.',
        'background_id' => $background->id,
        'image' => UploadedFile::fake()->image('memorial.jpg'),
    ]);

    expect(Pamphlet::query()->first()?->status)->toBe('draft');
});

it('redirects guests to login at checkout checkpoint', function () {
    $pamphlet = Pamphlet::factory()->create([
        'user_id' => null,
        'status' => 'draft',
    ]);

    $service = app(GuestPamphletDraftService::class);
    $token = $service->issueTokenForPamphlet($pamphlet);

    $response = $this->withCookie('guest_pamphlet_token', $token)->get(route('pamphlets.continue', $pamphlet));

    $response->assertRedirect(route('login'));
});

it('claims guest draft to authenticated user at checkpoint and proceeds to checkout', function () {
    $pamphlet = Pamphlet::factory()->create([
        'user_id' => null,
        'status' => 'draft',
    ]);
    $user = User::factory()->create();

    $service = app(GuestPamphletDraftService::class);
    $token = $service->issueTokenForPamphlet($pamphlet);

    $response = $this
        ->actingAs($user)
        ->withCookie('guest_pamphlet_token', $token)
        ->get(route('pamphlets.continue', $pamphlet));

    $response->assertRedirect(route('payments.checkout', $pamphlet));

    $pamphlet->refresh();
    expect($pamphlet->user_id)->toBe($user->id);
    expect($pamphlet->status)->toBe('pending_payment');
});

it('lets authenticated users continue directly to checkout without auth gate', function () {
    $pamphlet = Pamphlet::factory()->create([
        'status' => 'draft',
    ]);

    $response = $this->actingAs($pamphlet->user)->get(route('pamphlets.continue', $pamphlet));

    $response->assertRedirect(route('payments.checkout', $pamphlet));
});

it('prevents guests without token from viewing another draft', function () {
    $pamphlet = Pamphlet::factory()->create([
        'user_id' => null,
    ]);

    $response = $this->get(route('pamphlets.show', $pamphlet));

    $response->assertForbidden();
});

it('creates an initiated payment record on checkout', function () {
    $pamphlet = Pamphlet::factory()->create([
        'status' => 'pending_payment',
    ]);

    $response = $this->actingAs($pamphlet->user)->get(route('payments.checkout', $pamphlet));

    $response->assertSuccessful();

    $payment = Payment::query()->first();
    expect($payment)->not->toBeNull();
    expect($payment->status)->toBe('initiated');
});

it('marks payment and pamphlet as paid after successful return', function () {
    $pamphlet = Pamphlet::factory()->create([
        'status' => 'pending_payment',
        'paid_at' => null,
    ]);

    Payment::factory()->create([
        'pamphlet_id' => $pamphlet->id,
        'status' => 'initiated',
    ]);

    $response = $this->actingAs($pamphlet->user)->get(route('payments.return', $pamphlet));

    $response->assertRedirect(route('memorial.edit', $pamphlet));

    $pamphlet->refresh();

    expect($pamphlet->status)->toBe('paid');
    expect($pamphlet->pamphletQrCode)->not->toBeNull();
    expect($pamphlet->pamphletQrCode->target_url)->toContain('/memorial/');
});

it('shows public memorial page by slug for paid pamphlets', function () {
    $pamphlet = Pamphlet::factory()->create([
        'status' => 'paid',
    ]);

    $response = $this->get(route('memorial.public.show', $pamphlet->public_slug));

    $response->assertSuccessful();
});

it('prevents users from editing pamphlets they do not own', function () {
    $ownerPamphlet = Pamphlet::factory()->create([
        'status' => 'paid',
    ]);
    $otherUser = User::factory()->create();

    $response = $this->actingAs($otherUser)->get(route('memorial.edit', $ownerPamphlet));

    $response->assertForbidden();
});

it('removes selected memorial gallery images on update', function () {
    Storage::fake('public');

    $pamphlet = Pamphlet::factory()->create([
        'status' => 'paid',
    ]);

    $memorialPage = $pamphlet->memorialPage()->create([
        'funeral_programme' => 'Programme',
        'obituary' => 'Obituary',
        'hymns' => 'Hymns',
    ]);

    $imageToRemovePath = UploadedFile::fake()->image('remove.jpg')->store('memorial/gallery', 'public');
    $imageToKeepPath = UploadedFile::fake()->image('keep.jpg')->store('memorial/gallery', 'public');

    $imageToRemove = $memorialPage->galleryImages()->create([
        'image_path' => $imageToRemovePath,
        'caption' => null,
        'sort_order' => 0,
    ]);

    $imageToKeep = $memorialPage->galleryImages()->create([
        'image_path' => $imageToKeepPath,
        'caption' => null,
        'sort_order' => 1,
    ]);

    $response = $this->actingAs($pamphlet->user)->patch("/pamphlets/{$pamphlet->id}/memorial", [
        'font_family' => 'Georgia',
        'is_bold' => false,
        'is_italic' => false,
        'date_format' => 'd M Y',
        'funeral_programme' => 'Programme',
        'obituary' => 'Obituary',
        'hymns' => 'Hymns',
        'remove_gallery_image_ids' => [$imageToRemove->id],
    ]);

    $response->assertRedirect(route('memorial.public.show', $pamphlet->public_slug));

    expect($memorialPage->galleryImages()->whereKey($imageToRemove->id)->exists())->toBeFalse();
    expect($memorialPage->galleryImages()->whereKey($imageToKeep->id)->exists())->toBeTrue();

    Storage::disk('public')->assertMissing($imageToRemovePath);
    Storage::disk('public')->assertExists($imageToKeepPath);
});

it('allows an owner to open the print view', function () {
    $pamphlet = Pamphlet::factory()->create([
        'status' => 'published',
    ]);

    $response = $this->actingAs($pamphlet->user)->get(route('pamphlets.print', $pamphlet));

    $response->assertSuccessful();
});

it('prevents non-owners from opening the print view', function () {
    $pamphlet = Pamphlet::factory()->create([
        'status' => 'published',
    ]);
    $otherUser = User::factory()->create();

    $response = $this->actingAs($otherUser)->get(route('pamphlets.print', $pamphlet));

    $response->assertForbidden();
});

it('creates a pamphlet qr code when opening print view if missing', function () {
    $pamphlet = Pamphlet::factory()->create([
        'status' => 'published',
    ]);

    expect($pamphlet->pamphletQrCode)->toBeNull();

    $response = $this->actingAs($pamphlet->user)->get(route('pamphlets.print', $pamphlet));

    $response->assertSuccessful();

    $pamphlet->refresh();

    expect($pamphlet->pamphletQrCode)->not->toBeNull();
    expect($pamphlet->pamphletQrCode->target_url)->toBe(route('memorial.public.show', $pamphlet->public_slug));
    expect($pamphlet->pamphletQrCode->image_path)->toContain('api.qrserver.com');
});
