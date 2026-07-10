<?php

namespace App\Http\Controllers\Vault;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vault\StoreVaultAccessRequest;
use App\Models\PersonOfInterestVaultBeneficiary;
use App\Models\PersonOfInterestVaultMedia;
use App\Models\PersonOfInterestVaultPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class VaultAccessController extends Controller
{
    public function create(): InertiaResponse
    {
        return Inertia::render('vault/access/Request');
    }

    public function store(StoreVaultAccessRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $beneficiaries = PersonOfInterestVaultBeneficiary::query()
            ->where('email', $validated['email'])
            ->whereRelation('vault', 'status', 'released')
            ->get();

        foreach ($beneficiaries as $beneficiary) {
            if (! Hash::check($validated['access_code'], $beneficiary->access_code_hash)) {
                continue;
            }

            if ($beneficiary->first_accessed_at === null) {
                $beneficiary->update(['first_accessed_at' => now()]);
            }

            $request->session()->put('vault_beneficiary_id', $beneficiary->id);

            return redirect()->route('vault.access.show');
        }

        throw ValidationException::withMessages([
            'email' => 'We could not find a released vault matching those details.',
        ]);
    }

    public function show(Request $request): InertiaResponse
    {
        $beneficiaryId = $request->session()->get('vault_beneficiary_id');

        abort_if($beneficiaryId === null, 403);

        $beneficiary = PersonOfInterestVaultBeneficiary::query()
            ->with('vault.personOfInterest')
            ->find($beneficiaryId);

        abort_if($beneficiary === null || ! $beneficiary->vault->isReleased(), 403);

        $vault = $beneficiary->vault;

        $posts = $vault->posts()
            ->where(function ($query) use ($beneficiary) {
                $query->where('visibility', 'all')
                    ->orWhereHas('beneficiaries', function ($beneficiaryQuery) use ($beneficiary) {
                        $beneficiaryQuery->where(
                            'person_of_interest_vault_beneficiaries.id',
                            $beneficiary->id,
                        );
                    });
            })
            ->latest()
            ->get();

        return Inertia::render('vault/access/View', [
            'beneficiary' => [
                'full_name' => $beneficiary->full_name,
                'type' => $beneficiary->type,
            ],
            'vault' => [
                'name' => $vault->name,
                'person_display_name' => $vault->personOfInterest->display_name,
                'released_at' => $vault->released_at?->toDateString(),
            ],
            'posts' => $posts
                ->map(fn (PersonOfInterestVaultPost $post): array => [
                    'id' => $post->id,
                    'title' => $post->title,
                    'body' => $post->body,
                    'created_at' => $post->created_at?->toDateString(),
                ])
                ->values(),
            'media' => $vault->media()
                ->latest()
                ->get()
                ->map(fn (PersonOfInterestVaultMedia $media): array => [
                    'id' => $media->id,
                    'type' => $media->type,
                    'title' => $media->title,
                    'url' => Storage::disk('public')->url($media->file_path),
                    'mime_type' => $media->mime_type,
                    'file_size_bytes' => $media->file_size_bytes,
                ])
                ->values(),
        ]);
    }
}
