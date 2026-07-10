<?php

use App\Enums\PamphletStatus;
use App\Enums\TransactionStatus;
use App\Models\MemorialPagePamphlet;
use App\Models\MemorialPagePamphletBackground;
use App\Models\MemorialPagePamphletBackgroundCollection;
use App\Models\Transaction;
use App\Models\User;
use App\Services\GuestPamphletDraftService;
use App\Services\PayfastService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('allows guests to create a draft and view preview before auth', function () {
    Storage::fake('public');
    config()->set('memorial.bypass_payment_for_publish', true);

    $collection = MemorialPagePamphletBackgroundCollection::factory()->create(['slug' => 'adults']);
    $background = MemorialPagePamphletBackground::factory()->create([
        'collection_id' => $collection->id,
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

    $pamphlet = MemorialPagePamphlet::query()->first();

    expect($pamphlet)->not->toBeNull();
    expect($pamphlet->status)->toBe(PamphletStatus::Published);
    expect($pamphlet->owner())->toBeNull();
    expect($pamphlet->memorialPage?->personOfInterest?->guest_token_hash)->not->toBeNull();

    $response->assertRedirect(route('pamphlets.show', $pamphlet));
    $response->assertCookie('guest_pamphlet_token');
});

it('creates draft status when payment bypass is disabled', function () {
    Storage::fake('public');
    config()->set('memorial.bypass_payment_for_publish', false);

    $collection = MemorialPagePamphletBackgroundCollection::factory()->create(['slug' => 'adults']);
    $background = MemorialPagePamphletBackground::factory()->create([
        'collection_id' => $collection->id,
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

    expect(MemorialPagePamphlet::query()->first()?->status)->toBe(PamphletStatus::Draft);
});

it('redirects guests to login at checkout checkpoint', function () {
    $pamphlet = MemorialPagePamphlet::factory()->guest()->create([
        'status' => PamphletStatus::Draft,
    ]);

    $service = app(GuestPamphletDraftService::class);
    $token = $service->issueTokenForPamphlet($pamphlet);

    $response = $this->withCookie('guest_pamphlet_token', $token)->get(route('pamphlets.continue', $pamphlet));

    $response->assertRedirect(route('login'));
});

it('claims guest draft to authenticated user at checkpoint and proceeds to checkout', function () {
    $pamphlet = MemorialPagePamphlet::factory()->guest()->create([
        'status' => PamphletStatus::Draft,
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
    expect($pamphlet->owner()?->is($user))->toBeTrue();
    expect($pamphlet->status)->toBe(PamphletStatus::PendingPayment);
});

it('lets authenticated users continue directly to checkout without auth gate', function () {
    $pamphlet = MemorialPagePamphlet::factory()->create([
        'status' => PamphletStatus::Draft,
    ]);

    $response = $this->actingAs($pamphlet->owner())->get(route('pamphlets.continue', $pamphlet));

    $response->assertRedirect(route('payments.checkout', $pamphlet));
});

it('prevents guests without token from viewing another draft', function () {
    $pamphlet = MemorialPagePamphlet::factory()->guest()->create([
        'status' => PamphletStatus::Draft,
    ]);

    $response = $this->get(route('pamphlets.show', $pamphlet));

    $response->assertForbidden();
});

it('creates an initiated transaction record on checkout', function () {
    $pamphlet = MemorialPagePamphlet::factory()->create([
        'status' => PamphletStatus::PendingPayment,
    ]);

    $response = $this->actingAs($pamphlet->owner())->get(route('payments.checkout', $pamphlet));

    $response->assertSuccessful();

    $transaction = Transaction::query()->first();
    expect($transaction)->not->toBeNull();
    expect($transaction->status)->toBe(TransactionStatus::Initiated);
});

it('marks transaction and pamphlet as paid after a valid ITN notification', function () {
    Http::fake([
        'sandbox.payfast.co.za/eng/query/validate' => Http::response('VALID'),
    ]);

    config()->set('services.payfast.merchant_id', '10000100');
    config()->set('services.payfast.merchant_key', '46f0cd694581a');
    config()->set('services.payfast.passphrase', 'test-passphrase');
    config()->set('services.payfast.url', 'https://sandbox.payfast.co.za/eng/process');

    $pamphlet = MemorialPagePamphlet::factory()->create([
        'status' => PamphletStatus::PendingPayment,
        'paid_at' => null,
    ]);

    $transaction = Transaction::factory()->create([
        'payable_type' => MemorialPagePamphlet::class,
        'payable_id' => $pamphlet->id,
        'user_id' => $pamphlet->owner()?->id,
        'status' => TransactionStatus::Initiated,
        'amount_cents' => config('memorial.fixed_price_cents'),
    ]);

    $payload = [
        'm_payment_id' => $transaction->merchant_reference,
        'pf_payment_id' => '1089250',
        'payment_status' => 'COMPLETE',
        'item_name' => 'Memorial pamphlet',
        'amount_gross' => number_format($transaction->amount_cents / 100, 2, '.', ''),
        'merchant_id' => config('services.payfast.merchant_id'),
    ];

    $payfastService = app(PayfastService::class);
    $paramString = $payfastService->buildItnParameterString($payload);
    $payload['signature'] = md5($paramString.'&passphrase='.urlencode('test-passphrase'));

    $this->post(route('payments.notify', $pamphlet), $payload)->assertOk();

    $pamphlet->refresh();

    expect($pamphlet->status)->toBe(PamphletStatus::Paid);
    expect($pamphlet->memorialPage?->personOfInterest?->qr_code_path)->not->toBeNull();
});

it('shows public memorial page by slug for paid pamphlets', function () {
    $pamphlet = MemorialPagePamphlet::factory()->create([
        'status' => PamphletStatus::Paid,
    ]);

    $response = $this->get(route('memorial.public.show', $pamphlet->public_slug));

    $response->assertSuccessful();
});

it('prevents users from editing pamphlets they do not own', function () {
    $ownerPamphlet = MemorialPagePamphlet::factory()->create([
        'status' => PamphletStatus::Paid,
    ]);
    $otherUser = User::factory()->create();

    $response = $this->actingAs($otherUser)->get(route('memorial.edit', $ownerPamphlet));

    $response->assertForbidden();
});

it('removes selected memorial gallery images on update', function () {
    Storage::fake('public');

    $pamphlet = MemorialPagePamphlet::factory()->create([
        'status' => PamphletStatus::Paid,
    ]);

    $memorialPage = $pamphlet->memorialPage;
    $memorialPage->update([
        'funeral_programme' => 'Programme',
        'obituary' => 'Obituary',
        'hymns' => 'Hymns',
    ]);

    $imageToRemovePath = UploadedFile::fake()->image('remove.jpg')->store('memorial/gallery', 'public');
    $imageToKeepPath = UploadedFile::fake()->image('keep.jpg')->store('memorial/gallery', 'public');

    $imageToRemove = $memorialPage->images()->create([
        'image_path' => $imageToRemovePath,
        'caption' => null,
        'sort_order' => 0,
    ]);

    $imageToKeep = $memorialPage->images()->create([
        'image_path' => $imageToKeepPath,
        'caption' => null,
        'sort_order' => 1,
    ]);

    $response = $this->actingAs($pamphlet->owner())->patch("/pamphlets/{$pamphlet->id}/memorial", [
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

    expect($memorialPage->images()->whereKey($imageToRemove->id)->exists())->toBeFalse();
    expect($memorialPage->images()->whereKey($imageToKeep->id)->exists())->toBeTrue();

    Storage::disk('public')->assertMissing($imageToRemovePath);
    Storage::disk('public')->assertExists($imageToKeepPath);
});

it('allows an owner to open the print view', function () {
    $pamphlet = MemorialPagePamphlet::factory()->create([
        'status' => PamphletStatus::Published,
    ]);

    $response = $this->actingAs($pamphlet->owner())->get(route('pamphlets.print', $pamphlet));

    $response->assertSuccessful();
});

it('prevents non-owners from opening the print view', function () {
    $pamphlet = MemorialPagePamphlet::factory()->create([
        'status' => PamphletStatus::Published,
    ]);
    $otherUser = User::factory()->create();

    $response = $this->actingAs($otherUser)->get(route('pamphlets.print', $pamphlet));

    $response->assertForbidden();
});

it('creates a person of interest qr code when opening print view if missing', function () {
    $pamphlet = MemorialPagePamphlet::factory()->create([
        'status' => PamphletStatus::Published,
    ]);

    expect($pamphlet->memorialPage?->personOfInterest?->qr_code_path)->toBeNull();

    $response = $this->actingAs($pamphlet->owner())->get(route('pamphlets.print', $pamphlet));

    $response->assertSuccessful();

    $pamphlet->refresh();

    expect($pamphlet->memorialPage?->personOfInterest?->qr_code_path)->not->toBeNull();
    expect($pamphlet->memorialPage?->personOfInterest?->qr_code_path)->toContain('api.qrserver.com');
});
