<?php

namespace App\Http\Controllers\Vault;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vault\StoreVaultPostRequest;
use App\Http\Requests\Vault\UpdateVaultPostRequest;
use App\Models\PersonOfInterestVault;
use App\Models\PersonOfInterestVaultPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class VaultPostController extends Controller
{
    public function store(
        StoreVaultPostRequest $request,
        PersonOfInterestVault $vault
    ): RedirectResponse {
        abort_unless($vault->isManagedBy($request->user()), 403);
        abort_if($vault->isReleased(), 403);

        $validated = $request->validated();
        $beneficiaryIds = $this->validatedBeneficiaryIds($vault, $validated);

        $post = $vault->posts()->create([
            'author_user_id' => $request->user()->id,
            'title' => $validated['title'] ?? null,
            'body' => $validated['body'],
            'visibility' => $validated['visibility'],
        ]);

        $post->beneficiaries()->sync($beneficiaryIds);

        return redirect()
            ->route('vault.show', $vault)
            ->with('status', 'Post added to the vault.');
    }

    public function update(
        UpdateVaultPostRequest $request,
        PersonOfInterestVault $vault,
        PersonOfInterestVaultPost $post
    ): RedirectResponse {
        abort_unless($post->vault_id === $vault->id, 404);
        abort_unless($vault->isManagedBy($request->user()), 403);
        abort_if($vault->isReleased(), 403);

        $validated = $request->validated();
        $beneficiaryIds = $this->validatedBeneficiaryIds($vault, $validated);

        $post->update([
            'title' => $validated['title'] ?? null,
            'body' => $validated['body'],
            'visibility' => $validated['visibility'],
        ]);

        $post->beneficiaries()->sync($beneficiaryIds);

        return redirect()
            ->route('vault.show', $vault)
            ->with('status', 'Post updated.');
    }

    public function destroy(
        PersonOfInterestVault $vault,
        PersonOfInterestVaultPost $post
    ): RedirectResponse {
        abort_unless($post->vault_id === $vault->id, 404);
        abort_unless($vault->isManagedBy(request()->user()), 403);
        abort_if($vault->isReleased(), 403);

        $post->beneficiaries()->detach();
        $post->delete();

        return redirect()
            ->route('vault.show', $vault)
            ->with('status', 'Post deleted.');
    }

    /**
     * @param  array{visibility: string, beneficiary_ids?: array<int, string>}  $validated
     * @return array<int, string>
     */
    private function validatedBeneficiaryIds(PersonOfInterestVault $vault, array $validated): array
    {
        if ($validated['visibility'] !== 'selected') {
            return [];
        }

        $beneficiaryIds = array_values(array_unique($validated['beneficiary_ids'] ?? []));

        $validCount = $vault->beneficiaries()
            ->whereIn('id', $beneficiaryIds)
            ->count();

        if ($validCount !== count($beneficiaryIds)) {
            throw ValidationException::withMessages([
                'beneficiary_ids' => 'One or more selected beneficiaries do not belong to this vault.',
            ]);
        }

        return $beneficiaryIds;
    }
}
