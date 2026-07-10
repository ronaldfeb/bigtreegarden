<?php

use App\Enums\PamphletStatus;
use App\Models\MemorialPage;
use App\Models\MemorialPageMessage;
use App\Models\MemorialPagePamphlet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

/**
 * @param  array<string, mixed>  $attributes
 */
function createPublishedLiveMemorialPage(array $attributes = []): MemorialPage
{
    $pamphlet = MemorialPagePamphlet::factory()->create([
        'status' => PamphletStatus::Published,
    ]);

    $memorialPage = $pamphlet->memorialPage;
    $memorialPage->update($attributes);

    return $memorialPage->refresh();
}

it('renders the live page for a published memorial', function () {
    $memorialPage = createPublishedLiveMemorialPage([
        'active_day_date' => now()->toDateString(),
        'live_comments_enabled' => true,
    ]);

    $this->get(route('memorial.live.show', $memorialPage->public_slug))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('memorial/Live')
            ->where('isActiveDay', true)
            ->where('canPost', false)
            ->has('messagesUrl')
            ->has('postUrl')
        );
});

it('returns 404 for the live page of an unpublished memorial', function () {
    $pamphlet = MemorialPagePamphlet::factory()->create([
        'status' => PamphletStatus::Draft,
    ]);

    $this->get(route('memorial.live.show', $pamphlet->memorialPage->public_slug))
        ->assertNotFound();
});

it('returns approved live day messages as JSON in chronological order', function () {
    $memorialPage = createPublishedLiveMemorialPage();
    $author = User::factory()->create(['name' => 'Sipho Dlamini']);

    $older = MemorialPageMessage::factory()->create([
        'memorial_page_id' => $memorialPage->id,
        'author_user_id' => $author->id,
        'context' => 'live_day',
        'status' => 'approved',
        'body' => 'First message',
        'created_at' => now()->subMinutes(5),
    ]);

    $newer = MemorialPageMessage::factory()->create([
        'memorial_page_id' => $memorialPage->id,
        'author_user_id' => $author->id,
        'context' => 'live_day',
        'status' => 'approved',
        'body' => 'Second message',
        'created_at' => now()->subMinute(),
    ]);

    MemorialPageMessage::factory()->create([
        'memorial_page_id' => $memorialPage->id,
        'context' => 'live_day',
        'status' => 'pending',
    ]);

    MemorialPageMessage::factory()->create([
        'memorial_page_id' => $memorialPage->id,
        'context' => 'flowers',
        'status' => 'approved',
    ]);

    $response = $this->getJson(route('memorial.live.messages', $memorialPage->public_slug));

    $response->assertOk()
        ->assertJsonCount(2, 'messages')
        ->assertJsonStructure([
            'messages' => [
                '*' => ['id', 'author', 'body', 'image_url', 'posted_at'],
            ],
        ])
        ->assertJsonPath('messages.0.id', $older->id)
        ->assertJsonPath('messages.0.author', 'Sipho Dlamini')
        ->assertJsonPath('messages.1.id', $newer->id);

    expect($response->headers->get('Cache-Control'))->toContain('no-store');
});

it('filters messages posted after the given timestamp', function () {
    $memorialPage = createPublishedLiveMemorialPage();
    $author = User::factory()->create();

    MemorialPageMessage::factory()->create([
        'memorial_page_id' => $memorialPage->id,
        'author_user_id' => $author->id,
        'context' => 'live_day',
        'status' => 'approved',
        'created_at' => now()->subMinutes(10),
    ]);

    $recent = MemorialPageMessage::factory()->create([
        'memorial_page_id' => $memorialPage->id,
        'author_user_id' => $author->id,
        'context' => 'live_day',
        'status' => 'approved',
        'created_at' => now()->subMinute(),
    ]);

    $this->getJson(route('memorial.live.messages', [
        'slug' => $memorialPage->public_slug,
        'after' => now()->subMinutes(5)->toIso8601String(),
    ]))
        ->assertOk()
        ->assertJsonCount(1, 'messages')
        ->assertJsonPath('messages.0.id', $recent->id);
});

it('allows an authenticated user to post an auto-approved comment on the active day', function () {
    $memorialPage = createPublishedLiveMemorialPage([
        'active_day_date' => now()->toDateString(),
        'live_comments_enabled' => true,
    ]);

    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('memorial.live.store', $memorialPage->public_slug), [
        'body' => 'What a beautiful service.',
    ]);

    $response->assertRedirect();

    $message = MemorialPageMessage::query()->first();

    expect($message)->not->toBeNull();
    expect($message->context)->toBe('live_day');
    expect($message->status)->toBe('approved');
    expect($message->approved_at)->not->toBeNull();
    expect($message->author_user_id)->toBe($user->id);
    expect($message->body)->toBe('What a beautiful service.');
});

it('stores live day image uploads on the public disk', function () {
    Storage::fake('public');

    $memorialPage = createPublishedLiveMemorialPage([
        'active_day_date' => now()->toDateString(),
        'live_comments_enabled' => true,
    ]);

    $user = User::factory()->create();

    $this->actingAs($user)->post(route('memorial.live.store', $memorialPage->public_slug), [
        'image' => UploadedFile::fake()->image('service.jpg'),
    ])->assertRedirect();

    $message = MemorialPageMessage::query()->first();

    expect($message->type)->toBe('image');
    expect($message->image_path)->toStartWith('live/'.$memorialPage->id.'/');
    Storage::disk('public')->assertExists($message->image_path);
});

it('forbids posting when today is not the active day', function () {
    $memorialPage = createPublishedLiveMemorialPage([
        'active_day_date' => now()->addDay()->toDateString(),
        'live_comments_enabled' => true,
    ]);

    $this->actingAs(User::factory()->create())
        ->post(route('memorial.live.store', $memorialPage->public_slug), [
            'body' => 'Too early.',
        ])
        ->assertForbidden();

    expect(MemorialPageMessage::query()->count())->toBe(0);
});

it('forbids posting when live comments are disabled', function () {
    $memorialPage = createPublishedLiveMemorialPage([
        'active_day_date' => now()->toDateString(),
        'live_comments_enabled' => false,
    ]);

    $this->actingAs(User::factory()->create())
        ->post(route('memorial.live.store', $memorialPage->public_slug), [
            'body' => 'Comments are off.',
        ])
        ->assertForbidden();

    expect(MemorialPageMessage::query()->count())->toBe(0);
});

it('redirects guests who try to post a live comment to login', function () {
    $memorialPage = createPublishedLiveMemorialPage([
        'active_day_date' => now()->toDateString(),
        'live_comments_enabled' => true,
    ]);

    $this->post(route('memorial.live.store', $memorialPage->public_slug), [
        'body' => 'Guest message.',
    ])->assertRedirect(route('login'));

    expect(MemorialPageMessage::query()->count())->toBe(0);
});
