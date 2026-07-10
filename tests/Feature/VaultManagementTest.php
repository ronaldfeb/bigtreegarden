<?php

use App\Models\PersonOfInterest;
use App\Models\PersonOfInterestVault;
use App\Models\PersonOfInterestVaultBeneficiary;
use App\Models\PersonOfInterestVaultMedia;
use App\Models\PersonOfInterestVaultPost;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function vaultSubscribedUser(): User
{
    $user = User::factory()->create();
    Subscription::factory()->active()->create(['user_id' => $user->id]);

    return $user;
}

function personOfInterestOwnedBy(User $user): PersonOfInterest
{
    $personOfInterest = PersonOfInterest::factory()->create(['created_by_user_id' => $user->id]);
    $personOfInterest->users()->attach($user->id, ['role' => 'owner']);

    return $personOfInterest;
}

/**
 * @param  array<string, mixed>  $attributes
 */
function vaultOwnedBy(User $user, array $attributes = []): PersonOfInterestVault
{
    return PersonOfInterestVault::factory()->create(array_merge(
        ['person_of_interest_id' => personOfInterestOwnedBy($user)->id],
        $attributes,
    ));
}

/**
 * @return array<string, string>
 */
function validBeneficiaryPayload(array $overrides = []): array
{
    return array_merge([
        'type' => 'beneficiary',
        'full_name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'contact_number' => '+27 82 000 0000',
        'physical_address' => '1 Garden Road, Cape Town',
    ], $overrides);
}

it('redirects users without an active subscription to pricing', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('vault.index'))
        ->assertRedirect(route('pricing'));
});

it('lists vaults for the users persons of interest', function () {
    $user = vaultSubscribedUser();
    $vault = vaultOwnedBy($user);

    $this->actingAs($user)
        ->get(route('vault.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('vault/Index')
            ->has('personsOfInterest', 1)
            ->where('personsOfInterest.0.vault.id', $vault->id));
});

it('creates a sealed vault for an owned person of interest', function () {
    $user = vaultSubscribedUser();
    $personOfInterest = personOfInterestOwnedBy($user);

    $response = $this->actingAs($user)->post(route('vault.store'), [
        'person_of_interest_id' => $personOfInterest->id,
    ]);

    $vault = PersonOfInterestVault::query()
        ->where('person_of_interest_id', $personOfInterest->id)
        ->first();

    expect($vault)->not->toBeNull();
    expect($vault->status)->toBe('sealed');
    expect($vault->storage_limit_mb)->toBe(1024);
    expect($vault->name)->not->toBeNull();
    $response->assertRedirect(route('vault.show', $vault));
});

it('forbids creating a vault for a person of interest the user does not own', function () {
    $user = vaultSubscribedUser();
    $otherPersonOfInterest = PersonOfInterest::factory()->create();

    $this->actingAs($user)
        ->post(route('vault.store'), [
            'person_of_interest_id' => $otherPersonOfInterest->id,
        ])
        ->assertForbidden();
});

it('rejects creating a second vault for the same person of interest', function () {
    $user = vaultSubscribedUser();
    $vault = vaultOwnedBy($user);

    $this->actingAs($user)
        ->post(route('vault.store'), [
            'person_of_interest_id' => $vault->person_of_interest_id,
        ])
        ->assertSessionHasErrors('person_of_interest_id');
});

it('forbids viewing a vault the user does not manage', function () {
    $user = vaultSubscribedUser();
    $otherVault = PersonOfInterestVault::factory()->create();

    $this->actingAs($user)
        ->get(route('vault.show', $otherVault))
        ->assertForbidden();
});

it('stores a hashed access code and flashes the plaintext code once', function () {
    $user = vaultSubscribedUser();
    $vault = vaultOwnedBy($user);

    $response = $this->actingAs($user)->post(
        route('vault.beneficiaries.store', $vault),
        validBeneficiaryPayload(),
    );

    $beneficiary = $vault->beneficiaries()->first();

    expect($beneficiary)->not->toBeNull();

    $flashed = session('vault_access_code');

    expect($flashed)->toBeArray();
    expect($flashed['beneficiary_id'])->toBe($beneficiary->id);
    expect($flashed['code'])->toMatch('/^[A-Z0-9]{10}$/');
    expect($beneficiary->access_code_hash)->not->toBe($flashed['code']);
    expect(Hash::check($flashed['code'], $beneficiary->access_code_hash))->toBeTrue();
    expect($beneficiary->access_code_hint)->toStartWith(substr($flashed['code'], 0, 2));
    expect($beneficiary->access_code_hint)->toEndWith(substr($flashed['code'], -2));
    $response->assertRedirect(route('vault.show', $vault));
});

it('rejects adding a second executor to a vault', function () {
    $user = vaultSubscribedUser();
    $vault = vaultOwnedBy($user);
    PersonOfInterestVaultBeneficiary::factory()->executor()->create(['vault_id' => $vault->id]);

    $this->actingAs($user)
        ->post(
            route('vault.beneficiaries.store', $vault),
            validBeneficiaryPayload(['type' => 'executor']),
        )
        ->assertSessionHasErrors('type');

    expect($vault->beneficiaries()->where('type', 'executor')->count())->toBe(1);
});

it('returns 404 for a beneficiary belonging to another vault', function () {
    $user = vaultSubscribedUser();
    $vault = vaultOwnedBy($user);
    $foreignBeneficiary = PersonOfInterestVaultBeneficiary::factory()->create();

    $this->actingAs($user)
        ->patch(
            route('vault.beneficiaries.update', [$vault, $foreignBeneficiary]),
            validBeneficiaryPayload(),
        )
        ->assertNotFound();
});

it('uploads media within the storage limit', function () {
    Storage::fake('public');

    $user = vaultSubscribedUser();
    $vault = vaultOwnedBy($user);

    $this->actingAs($user)
        ->post(route('vault.media.store', $vault), [
            'title' => 'A photo',
            'file' => UploadedFile::fake()->image('memory.jpg')->size(500),
        ])
        ->assertRedirect(route('vault.show', $vault));

    $media = $vault->media()->first();

    expect($media)->not->toBeNull();
    expect($media->type)->toBe('image');
    expect($media->file_path)->toStartWith("vaults/{$vault->id}/");
    Storage::disk('public')->assertExists($media->file_path);
});

it('rejects media uploads that exceed the vault storage limit', function () {
    Storage::fake('public');

    $user = vaultSubscribedUser();
    $vault = vaultOwnedBy($user, ['storage_limit_mb' => 1]);

    $this->actingAs($user)
        ->post(route('vault.media.store', $vault), [
            'file' => UploadedFile::fake()->create('large.pdf', 2048, 'application/pdf'),
        ])
        ->assertSessionHasErrors('file');

    expect($vault->media()->count())->toBe(0);
});

it('deletes media and removes the file from disk', function () {
    Storage::fake('public');

    $user = vaultSubscribedUser();
    $vault = vaultOwnedBy($user);
    $filePath = UploadedFile::fake()->image('memory.jpg')->store("vaults/{$vault->id}", 'public');
    $media = PersonOfInterestVaultMedia::factory()->create([
        'vault_id' => $vault->id,
        'uploaded_by_user_id' => $user->id,
        'file_path' => $filePath,
    ]);

    $this->actingAs($user)
        ->delete(route('vault.media.destroy', [$vault, $media]))
        ->assertRedirect(route('vault.show', $vault));

    Storage::disk('public')->assertMissing($filePath);
    expect($vault->media()->count())->toBe(0);
});

it('creates a post visible to selected beneficiaries', function () {
    $user = vaultSubscribedUser();
    $vault = vaultOwnedBy($user);
    $beneficiary = PersonOfInterestVaultBeneficiary::factory()->create(['vault_id' => $vault->id]);

    $this->actingAs($user)
        ->post(route('vault.posts.store', $vault), [
            'title' => 'For you only',
            'body' => 'A private message.',
            'visibility' => 'selected',
            'beneficiary_ids' => [$beneficiary->id],
        ])
        ->assertRedirect(route('vault.show', $vault));

    $post = $vault->posts()->first();

    expect($post)->not->toBeNull();
    expect($post->visibility)->toBe('selected');
    expect($post->beneficiaries()->pluck('person_of_interest_vault_beneficiaries.id')->all())
        ->toBe([$beneficiary->id]);
});

it('rejects posts targeting beneficiaries of another vault', function () {
    $user = vaultSubscribedUser();
    $vault = vaultOwnedBy($user);
    $foreignBeneficiary = PersonOfInterestVaultBeneficiary::factory()->create();

    $this->actingAs($user)
        ->post(route('vault.posts.store', $vault), [
            'body' => 'A private message.',
            'visibility' => 'selected',
            'beneficiary_ids' => [$foreignBeneficiary->id],
        ])
        ->assertSessionHasErrors('beneficiary_ids');

    expect($vault->posts()->count())->toBe(0);
});

it('releases a sealed vault', function () {
    $user = vaultSubscribedUser();
    $vault = vaultOwnedBy($user);

    $this->actingAs($user)
        ->post(route('vault.release', $vault))
        ->assertRedirect(route('vault.show', $vault));

    $vault->refresh();

    expect($vault->status)->toBe('released');
    expect($vault->released_at)->not->toBeNull();
});

it('makes a released vault read-only for the owner', function () {
    $user = vaultSubscribedUser();
    $vault = vaultOwnedBy($user, ['status' => 'released', 'released_at' => now()]);
    $beneficiary = PersonOfInterestVaultBeneficiary::factory()->create(['vault_id' => $vault->id]);
    $post = PersonOfInterestVaultPost::factory()->create([
        'vault_id' => $vault->id,
        'author_user_id' => $user->id,
    ]);

    $this->actingAs($user)
        ->post(route('vault.beneficiaries.store', $vault), validBeneficiaryPayload())
        ->assertForbidden();

    $this->actingAs($user)
        ->delete(route('vault.beneficiaries.destroy', [$vault, $beneficiary]))
        ->assertForbidden();

    $this->actingAs($user)
        ->post(route('vault.posts.store', $vault), [
            'body' => 'Too late.',
            'visibility' => 'all',
        ])
        ->assertForbidden();

    $this->actingAs($user)
        ->delete(route('vault.posts.destroy', [$vault, $post]))
        ->assertForbidden();
});
