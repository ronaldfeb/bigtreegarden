<?php

namespace App\Http\Controllers\Vault;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vault\StoreVaultRequest;
use App\Models\PersonOfInterest;
use App\Models\PersonOfInterestVault;
use App\Models\PersonOfInterestVaultBeneficiary;
use App\Models\PersonOfInterestVaultMedia;
use App\Models\PersonOfInterestVaultPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class VaultController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $personsOfInterest = $request->user()->personsOfInterest()->get();

        $vaults = PersonOfInterestVault::query()
            ->whereIn('person_of_interest_id', $personsOfInterest->pluck('id'))
            ->withCount(['beneficiaries', 'media', 'posts'])
            ->withSum('media', 'file_size_bytes')
            ->get()
            ->keyBy('person_of_interest_id');

        return Inertia::render('vault/Index', [
            'personsOfInterest' => $personsOfInterest
                ->map(function (PersonOfInterest $personOfInterest) use ($vaults): array {
                    $vault = $vaults->get($personOfInterest->id);

                    return [
                        'id' => $personOfInterest->id,
                        'display_name' => $personOfInterest->display_name,
                        'vault' => $vault === null ? null : [
                            'id' => $vault->id,
                            'name' => $vault->name,
                            'status' => $vault->status,
                            'released_at' => $vault->released_at?->toDateString(),
                            'storage_limit_mb' => $vault->storage_limit_mb,
                            'storage_used_bytes' => (int) ($vault->media_sum_file_size_bytes ?? 0),
                            'beneficiaries_count' => $vault->beneficiaries_count,
                            'media_count' => $vault->media_count,
                            'posts_count' => $vault->posts_count,
                        ],
                    ];
                })
                ->values(),
        ]);
    }

    public function store(StoreVaultRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $personOfInterest = $request->user()
            ->personsOfInterest()
            ->whereKey($validated['person_of_interest_id'])
            ->first();

        abort_unless($personOfInterest !== null, 403);

        $vaultExists = PersonOfInterestVault::query()
            ->where('person_of_interest_id', $personOfInterest->id)
            ->exists();

        if ($vaultExists) {
            throw ValidationException::withMessages([
                'person_of_interest_id' => 'A vault already exists for this person.',
            ]);
        }

        $vault = PersonOfInterestVault::query()->create([
            'person_of_interest_id' => $personOfInterest->id,
            'name' => $validated['name']
                ?? ($personOfInterest->display_name !== null
                    ? "{$personOfInterest->display_name}'s Vault"
                    : 'Digital Vault'),
            'status' => 'sealed',
            'storage_limit_mb' => 1024,
        ]);

        return redirect()
            ->route('vault.show', $vault)
            ->with('status', 'Vault created.');
    }

    public function show(Request $request, PersonOfInterestVault $vault): InertiaResponse
    {
        abort_unless($vault->isManagedBy($request->user()), 403);

        $vault->load(['personOfInterest', 'beneficiaries', 'media', 'posts.beneficiaries']);

        return Inertia::render('vault/Show', [
            'vault' => [
                'id' => $vault->id,
                'name' => $vault->name,
                'status' => $vault->status,
                'released_at' => $vault->released_at?->toDateString(),
                'storage_limit_mb' => $vault->storage_limit_mb,
                'storage_used_bytes' => $vault->usedStorageBytes(),
                'person_display_name' => $vault->personOfInterest->display_name,
                'beneficiaries' => $vault->beneficiaries
                    ->map(fn (PersonOfInterestVaultBeneficiary $beneficiary): array => [
                        'id' => $beneficiary->id,
                        'type' => $beneficiary->type,
                        'full_name' => $beneficiary->full_name,
                        'email' => $beneficiary->email,
                        'contact_number' => $beneficiary->contact_number,
                        'physical_address' => $beneficiary->physical_address,
                        'access_code_hint' => $beneficiary->access_code_hint,
                        'first_accessed_at' => $beneficiary->first_accessed_at?->toDateTimeString(),
                    ])
                    ->values(),
                'media' => $vault->media
                    ->map(fn (PersonOfInterestVaultMedia $media): array => [
                        'id' => $media->id,
                        'type' => $media->type,
                        'title' => $media->title,
                        'url' => Storage::disk('public')->url($media->file_path),
                        'mime_type' => $media->mime_type,
                        'file_size_bytes' => $media->file_size_bytes,
                        'created_at' => $media->created_at?->toDateTimeString(),
                    ])
                    ->values(),
                'posts' => $vault->posts
                    ->map(fn (PersonOfInterestVaultPost $post): array => [
                        'id' => $post->id,
                        'title' => $post->title,
                        'body' => $post->body,
                        'visibility' => $post->visibility,
                        'beneficiary_ids' => $post->beneficiaries->pluck('id')->values(),
                        'created_at' => $post->created_at?->toDateTimeString(),
                    ])
                    ->values(),
            ],
        ]);
    }

    public function release(Request $request, PersonOfInterestVault $vault): RedirectResponse
    {
        abort_unless($vault->isManagedBy($request->user()), 403);

        if ($vault->isReleased()) {
            return redirect()
                ->route('vault.show', $vault)
                ->with('status', 'This vault has already been released.');
        }

        $vault->update([
            'status' => 'released',
            'released_at' => now(),
        ]);

        return redirect()
            ->route('vault.show', $vault)
            ->with('status', 'The vault has been released to its beneficiaries.');
    }
}
