<?php

use App\Models\PersonOfInterestVault;
use App\Models\PersonOfInterestVaultBeneficiary;
use App\Models\PersonOfInterestVaultMedia;
use App\Models\PersonOfInterestVaultPost;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function releasedVaultBeneficiary(string $plainCode = 'ABCD123456'): PersonOfInterestVaultBeneficiary
{
    $vault = PersonOfInterestVault::factory()->released()->create();

    return PersonOfInterestVaultBeneficiary::factory()
        ->withAccessCode($plainCode)
        ->create(['vault_id' => $vault->id]);
}

it('renders the access request form', function () {
    $this->get(route('vault.access.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('vault/access/Request'));
});

it('grants access with a valid email and access code on a released vault', function () {
    $beneficiary = releasedVaultBeneficiary('ABCD123456');

    $response = $this->post(route('vault.access.store'), [
        'email' => $beneficiary->email,
        'access_code' => 'ABCD123456',
    ]);

    $response->assertRedirect(route('vault.access.show'));
    $response->assertSessionHas('vault_beneficiary_id', $beneficiary->id);

    expect($beneficiary->refresh()->first_accessed_at)->not->toBeNull();
});

it('keeps the original first accessed timestamp on repeat logins', function () {
    $firstAccess = now()->subDays(3)->startOfSecond();
    $beneficiary = releasedVaultBeneficiary('ABCD123456');
    $beneficiary->update(['first_accessed_at' => $firstAccess]);

    $this->post(route('vault.access.store'), [
        'email' => $beneficiary->email,
        'access_code' => 'ABCD123456',
    ])->assertRedirect(route('vault.access.show'));

    expect($beneficiary->refresh()->first_accessed_at->equalTo($firstAccess))->toBeTrue();
});

it('rejects an invalid access code with a generic error', function () {
    $beneficiary = releasedVaultBeneficiary('ABCD123456');

    $response = $this->from(route('vault.access.create'))->post(route('vault.access.store'), [
        'email' => $beneficiary->email,
        'access_code' => 'WRONGCODE1',
    ]);

    $response->assertSessionHasErrors('email');
    $response->assertSessionMissing('vault_beneficiary_id');
});

it('rejects access to a sealed vault even with valid credentials', function () {
    $vault = PersonOfInterestVault::factory()->create(['status' => 'sealed']);
    $beneficiary = PersonOfInterestVaultBeneficiary::factory()
        ->withAccessCode('ABCD123456')
        ->create(['vault_id' => $vault->id]);

    $response = $this->post(route('vault.access.store'), [
        'email' => $beneficiary->email,
        'access_code' => 'ABCD123456',
    ]);

    $response->assertSessionHasErrors('email');
    $response->assertSessionMissing('vault_beneficiary_id');
});

it('aborts the vault view without an access session', function () {
    $this->get(route('vault.access.show'))->assertForbidden();
});

it('aborts the vault view if the vault is no longer released', function () {
    $vault = PersonOfInterestVault::factory()->create(['status' => 'sealed']);
    $beneficiary = PersonOfInterestVaultBeneficiary::factory()->create(['vault_id' => $vault->id]);

    $this->withSession(['vault_beneficiary_id' => $beneficiary->id])
        ->get(route('vault.access.show'))
        ->assertForbidden();
});

it('shows only posts visible to the beneficiary', function () {
    $vault = PersonOfInterestVault::factory()->released()->create();
    $beneficiary = PersonOfInterestVaultBeneficiary::factory()->create(['vault_id' => $vault->id]);
    $otherBeneficiary = PersonOfInterestVaultBeneficiary::factory()->create(['vault_id' => $vault->id]);

    $publicPost = PersonOfInterestVaultPost::factory()->create([
        'vault_id' => $vault->id,
        'visibility' => 'all',
    ]);

    $postForBeneficiary = PersonOfInterestVaultPost::factory()->create([
        'vault_id' => $vault->id,
        'visibility' => 'selected',
    ]);
    $postForBeneficiary->beneficiaries()->sync([$beneficiary->id]);

    $postForOther = PersonOfInterestVaultPost::factory()->create([
        'vault_id' => $vault->id,
        'visibility' => 'selected',
    ]);
    $postForOther->beneficiaries()->sync([$otherBeneficiary->id]);

    $this->withSession(['vault_beneficiary_id' => $beneficiary->id])
        ->get(route('vault.access.show'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('vault/access/View')
            ->has('posts', 2)
            ->where('posts', fn ($posts) => collect($posts)->pluck('id')
                ->contains($publicPost->id))
            ->where('posts', fn ($posts) => collect($posts)->pluck('id')
                ->contains($postForBeneficiary->id))
            ->where('posts', fn ($posts) => ! collect($posts)->pluck('id')
                ->contains($postForOther->id)));
});

it('lists all vault media with download urls for the beneficiary', function () {
    $vault = PersonOfInterestVault::factory()->released()->create();
    $beneficiary = PersonOfInterestVaultBeneficiary::factory()->create(['vault_id' => $vault->id]);
    PersonOfInterestVaultMedia::factory()->count(2)->create(['vault_id' => $vault->id]);

    $this->withSession(['vault_beneficiary_id' => $beneficiary->id])
        ->get(route('vault.access.show'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('vault/access/View')
            ->has('media', 2)
            ->has('media.0.url'));
});
