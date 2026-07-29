<?php

use App\Enums\PamphletStatus;
use App\Enums\TransactionStatus;
use App\Models\MemorialPagePamphlet;
use App\Models\MemorialPagePamphletBackground;
use App\Models\MemorialPagePamphletBackgroundCollection;
use App\Models\SubscriptionPackage;
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
    Storage::fake(config('filesystems.media'));
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
        'heading_color' => '#FFFFFF',
        'name_color' => '#F5F5F5',
        'short_text_color' => '#EEEEEE',
        'dates_color' => '#111111',
        'image' => UploadedFile::fake()->image('memorial.jpg'),
    ]);

    $pamphlet = MemorialPagePamphlet::query()->first();

    expect($pamphlet)->not->toBeNull();
    expect($pamphlet->status)->toBe(PamphletStatus::Published);
    expect($pamphlet->owner())->toBeNull();
    expect($pamphlet->memorialPage?->personOfInterest?->guest_token_hash)->not->toBeNull();
    expect($pamphlet->style?->heading_color)->toBe('#FFFFFF');
    expect($pamphlet->style?->name_color)->toBe('#F5F5F5');
    expect($pamphlet->style?->short_text_color)->toBe('#EEEEEE');
    expect($pamphlet->style?->dates_color)->toBe('#111111');

    $response->assertRedirect(route('pamphlets.show', $pamphlet));
    $response->assertCookie('guest_pamphlet_token');
});

it('rejects invalid pamphlet text colors', function () {
    Storage::fake(config('filesystems.media'));

    $collection = MemorialPagePamphletBackgroundCollection::factory()->create(['slug' => 'adults']);
    $background = MemorialPagePamphletBackground::factory()->create([
        'collection_id' => $collection->id,
    ]);

    $response = $this->from(route('pamphlets.create'))->post('/pamphlets', [
        'heading' => 'Celebrating Life',
        'person_full_name' => 'John Doe',
        'date_of_birth' => '1970-01-01',
        'date_of_passing' => '2024-01-01',
        'date_format' => 'd M Y',
        'image_shape' => 'square',
        'image_crop_mode' => 'cover',
        'short_text' => 'Beloved father and friend.',
        'background_id' => $background->id,
        'heading_color' => 'white',
        'name_color' => '#FFF',
        'short_text_color' => '#GGGGGG',
        'dates_color' => 'black',
        'image' => UploadedFile::fake()->image('memorial.jpg'),
    ]);

    $response->assertRedirect(route('pamphlets.create'));
    $response->assertSessionHasErrors(['heading_color', 'name_color', 'short_text_color', 'dates_color']);
    expect(MemorialPagePamphlet::query()->count())->toBe(0);
});

it('creates draft status when payment bypass is disabled', function () {
    Storage::fake(config('filesystems.media'));
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
        'heading_color' => '#FFFFFF',
        'name_color' => '#FFFFFF',
        'short_text_color' => '#FFFFFF',
        'dates_color' => '#000000',
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
    SubscriptionPackage::factory()->create([
        'slug' => 'memorial-page',
        'billing_interval' => 'once_off',
        'price_cents' => 69900,
        'currency' => 'ZAR',
        'is_active' => true,
    ]);

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
    SubscriptionPackage::factory()->create([
        'slug' => 'memorial-page',
        'billing_interval' => 'once_off',
        'price_cents' => 69900,
        'currency' => 'ZAR',
        'is_active' => true,
    ]);

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
    SubscriptionPackage::factory()->create([
        'slug' => 'memorial-page',
        'billing_interval' => 'once_off',
        'price_cents' => 69900,
        'currency' => 'ZAR',
        'is_active' => true,
    ]);

    $pamphlet = MemorialPagePamphlet::factory()->create([
        'status' => PamphletStatus::PendingPayment,
    ]);

    $response = $this->actingAs($pamphlet->owner())->get(route('payments.checkout', $pamphlet));

    $response->assertSuccessful();

    $transaction = Transaction::query()->first();
    expect($transaction)->not->toBeNull();
    expect($transaction->status)->toBe(TransactionStatus::Initiated);
    expect($transaction->amount_cents)->toBe(69900);
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
        'amount_cents' => 69900,
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
        'remove_gallery_image_ids' => [$imageToRemove->id],
    ]);

    $response->assertRedirect(route('memorial.public.show', $pamphlet->public_slug));

    expect($memorialPage->images()->whereKey($imageToRemove->id)->exists())->toBeFalse();
    expect($memorialPage->images()->whereKey($imageToKeep->id)->exists())->toBeTrue();

    Storage::disk('public')->assertMissing($imageToRemovePath);
    Storage::disk('public')->assertExists($imageToKeepPath);
});

it('saves and updates dynamic sections on memorial page update', function () {
    $pamphlet = MemorialPagePamphlet::factory()->create([
        'status' => PamphletStatus::Paid,
    ]);

    $memorialPage = $pamphlet->memorialPage;

    $sectionToUpdate = $memorialPage->sections()->create([
        'title' => 'Obituary',
        'body' => 'Original obituary text.',
        'sort_order' => 0,
    ]);

    $sectionToRemove = $memorialPage->sections()->create([
        'title' => 'Hymns',
        'body' => 'Amazing Grace',
        'sort_order' => 1,
    ]);

    $response = $this->actingAs($pamphlet->owner())->patch("/pamphlets/{$pamphlet->id}/memorial", [
        'font_family' => 'Georgia',
        'is_bold' => false,
        'is_italic' => false,
        'date_format' => 'd M Y',
        'sections' => [
            [
                'id' => $sectionToUpdate->id,
                'title' => 'Obituary (updated)',
                'body' => 'Updated obituary text.',
            ],
            [
                'title' => 'Tributes',
                'body' => 'A new tributes section.',
            ],
        ],
        'remove_section_ids' => [$sectionToRemove->id],
    ]);

    $response->assertRedirect(route('memorial.public.show', $pamphlet->public_slug));

    $sections = $memorialPage->sections()->orderBy('sort_order')->get();

    expect($sections)->toHaveCount(2);
    expect($sections[0]->id)->toBe($sectionToUpdate->id);
    expect($sections[0]->title)->toBe('Obituary (updated)');
    expect($sections[0]->body)->toBe('Updated obituary text.');
    expect($sections[0]->sort_order)->toBe(0);
    expect($sections[1]->title)->toBe('Tributes');
    expect($sections[1]->body)->toBe('A new tributes section.');
    expect($sections[1]->sort_order)->toBe(1);
    expect($memorialPage->sections()->whereKey($sectionToRemove->id)->exists())->toBeFalse();
});

it('allows an owner to open the print view', function () {
    Storage::fake(config('filesystems.media'));

    $imagePath = 'pamphlets/images/memorial.jpg';
    Storage::disk(config('filesystems.media'))->put($imagePath, 'fake-image');

    $pamphlet = MemorialPagePamphlet::factory()->create([
        'status' => PamphletStatus::Published,
        'uploaded_image_path' => $imagePath,
    ]);

    $pamphlet->style()->update([
        'heading_color' => '#112233',
        'name_color' => '#AABBCC',
        'short_text_color' => '#445566',
        'dates_color' => '#778899',
    ]);

    $response = $this->actingAs($pamphlet->owner())->get(route('pamphlets.print', $pamphlet));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('pamphlets/Print')
        ->where('pamphlet.uploaded_image_url', '/storage/'.$imagePath)
        ->where('pamphlet.heading_color', '#112233')
        ->where('pamphlet.name_color', '#AABBCC')
        ->where('pamphlet.short_text_color', '#445566')
        ->where('pamphlet.dates_color', '#778899')
    );
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

it('includes style colors and pricing on the pamphlet review page', function () {
    Storage::fake(config('filesystems.media'));

    $imagePath = 'pamphlets/images/review.jpg';
    Storage::disk(config('filesystems.media'))->put($imagePath, 'fake-image');

    SubscriptionPackage::factory()->create([
        'slug' => 'memorial-page',
        'name' => 'Memorial Page',
        'billing_interval' => 'once_off',
        'price_cents' => 69900,
        'currency' => 'ZAR',
        'is_active' => true,
    ]);

    $pamphlet = MemorialPagePamphlet::factory()->create([
        'status' => PamphletStatus::Draft,
        'uploaded_image_path' => $imagePath,
    ]);

    $pamphlet->style()->update([
        'heading_color' => '#112233',
        'name_color' => '#AABBCC',
        'short_text_color' => '#445566',
        'dates_color' => '#778899',
    ]);

    $response = $this->actingAs($pamphlet->owner())->get(route('pamphlets.show', $pamphlet));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('pamphlets/Show')
        ->where('pamphlet.heading_color', '#112233')
        ->where('pamphlet.name_color', '#AABBCC')
        ->where('pamphlet.short_text_color', '#445566')
        ->where('pamphlet.dates_color', '#778899')
        ->where('pamphlet.uploaded_image_url', '/storage/'.$imagePath)
        ->where('pricing.price_cents', 69900)
        ->where('pricing.currency', 'ZAR')
    );
});

it('allows an owner to edit and update a draft pamphlet', function () {
    Storage::fake(config('filesystems.media'));

    $collection = MemorialPagePamphletBackgroundCollection::factory()->create(['slug' => 'adults']);
    $background = MemorialPagePamphletBackground::factory()->create([
        'collection_id' => $collection->id,
    ]);
    $newBackground = MemorialPagePamphletBackground::factory()->create([
        'collection_id' => $collection->id,
    ]);

    $imagePath = 'pamphlets/images/existing.jpg';
    Storage::disk(config('filesystems.media'))->put($imagePath, 'fake-image');

    $pamphlet = MemorialPagePamphlet::factory()->create([
        'status' => PamphletStatus::Draft,
        'background_id' => $background->id,
        'uploaded_image_path' => $imagePath,
        'heading' => 'Original heading',
    ]);

    $editResponse = $this->actingAs($pamphlet->owner())->get(route('pamphlets.edit', $pamphlet));

    $editResponse->assertSuccessful();
    $editResponse->assertInertia(fn ($page) => $page
        ->component('pamphlets/CreatePamphlet')
        ->where('pamphlet.id', $pamphlet->id)
        ->where('pamphlet.heading', 'Original heading')
    );

    $updateResponse = $this->actingAs($pamphlet->owner())->put(route('pamphlets.update', $pamphlet), [
        'heading' => 'Updated heading',
        'person_full_name' => 'Updated Person',
        'date_of_birth' => '1975-06-15',
        'date_of_passing' => '2024-03-01',
        'date_format' => 'd M Y',
        'image_shape' => 'circle',
        'image_crop_mode' => 'contain',
        'short_text' => 'Updated memorial text.',
        'background_id' => $newBackground->id,
        'heading_color' => '#010101',
        'name_color' => '#020202',
        'short_text_color' => '#030303',
        'dates_color' => '#040404',
        'font_family' => 'Georgia',
    ]);

    $updateResponse->assertRedirect(route('pamphlets.show', $pamphlet));

    $pamphlet->refresh();
    $pamphlet->load(['style', 'memorialPage.personOfInterest']);

    expect($pamphlet->heading)->toBe('Updated heading');
    expect($pamphlet->person_full_name)->toBe('Updated Person');
    expect($pamphlet->background_id)->toBe($newBackground->id);
    expect($pamphlet->image_shape)->toBe('circle');
    expect($pamphlet->style?->heading_color)->toBe('#010101');
    expect($pamphlet->style?->dates_color)->toBe('#040404');
});

it('prevents editing a paid pamphlet', function () {
    $pamphlet = MemorialPagePamphlet::factory()->create([
        'status' => PamphletStatus::Paid,
        'paid_at' => now(),
    ]);

    $this->actingAs($pamphlet->owner())
        ->get(route('pamphlets.edit', $pamphlet))
        ->assertForbidden();

    $this->actingAs($pamphlet->owner())
        ->put(route('pamphlets.update', $pamphlet), [
            'heading' => 'Nope',
            'person_full_name' => 'Nope',
            'date_of_birth' => '1970-01-01',
            'date_of_passing' => '2024-01-01',
            'date_format' => 'd M Y',
            'image_shape' => 'square',
            'image_crop_mode' => 'cover',
            'short_text' => 'Nope',
            'background_id' => $pamphlet->background_id,
            'heading_color' => '#000000',
            'name_color' => '#000000',
            'short_text_color' => '#000000',
            'dates_color' => '#000000',
        ])
        ->assertForbidden();
});

it('allows a guest draft owner to edit with their cookie', function () {
    $pamphlet = MemorialPagePamphlet::factory()->guest()->create([
        'status' => PamphletStatus::Draft,
    ]);

    $service = app(GuestPamphletDraftService::class);
    $token = $service->issueTokenForPamphlet($pamphlet);

    $response = $this
        ->withCookie('guest_pamphlet_token', $token)
        ->get(route('pamphlets.edit', $pamphlet));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('pamphlets/CreatePamphlet')
        ->where('pamphlet.id', $pamphlet->id)
    );
});

it('passes amount, currency, and pamphlet preview to the checkout page', function () {
    SubscriptionPackage::factory()->create([
        'slug' => 'memorial-page',
        'billing_interval' => 'once_off',
        'price_cents' => 69900,
        'currency' => 'ZAR',
        'is_active' => true,
    ]);

    $pamphlet = MemorialPagePamphlet::factory()->create([
        'status' => PamphletStatus::PendingPayment,
        'heading' => 'Checkout Preview Heading',
    ]);

    $response = $this->actingAs($pamphlet->owner())->get(route('payments.checkout', $pamphlet));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('payments/Checkout')
        ->where('amount_cents', 69900)
        ->where('currency', 'ZAR')
        ->where('autoSubmit', true)
        ->where('pamphlet.heading', 'Checkout Preview Heading')
        ->has('pricing')
    );
});
