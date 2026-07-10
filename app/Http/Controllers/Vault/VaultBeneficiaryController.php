<?php

namespace App\Http\Controllers\Vault;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vault\StoreVaultBeneficiaryRequest;
use App\Http\Requests\Vault\UpdateVaultBeneficiaryRequest;
use App\Models\PersonOfInterestVault;
use App\Models\PersonOfInterestVaultBeneficiary;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class VaultBeneficiaryController extends Controller
{
    public function store(
        StoreVaultBeneficiaryRequest $request,
        PersonOfInterestVault $vault
    ): RedirectResponse {
        abort_unless($vault->isManagedBy($request->user()), 403);
        abort_if($vault->isReleased(), 403);

        $validated = $request->validated();

        $this->ensureVaultHasNoOtherExecutor($vault, $validated['type']);

        $plainCode = $this->generateAccessCode();

        $beneficiary = $vault->beneficiaries()->create([
            'type' => $validated['type'],
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'contact_number' => $validated['contact_number'],
            'physical_address' => $validated['physical_address'],
            'access_code_hash' => Hash::make($plainCode),
            'access_code_hint' => $this->accessCodeHint($plainCode),
        ]);

        return redirect()
            ->route('vault.show', $vault)
            ->with('status', "Beneficiary added. Share their access code now — it won't be shown again.")
            ->with('vault_access_code', [
                'beneficiary_id' => $beneficiary->id,
                'beneficiary_name' => $beneficiary->full_name,
                'code' => $plainCode,
            ]);
    }

    public function update(
        UpdateVaultBeneficiaryRequest $request,
        PersonOfInterestVault $vault,
        PersonOfInterestVaultBeneficiary $beneficiary
    ): RedirectResponse {
        abort_unless($beneficiary->vault_id === $vault->id, 404);
        abort_unless($vault->isManagedBy($request->user()), 403);
        abort_if($vault->isReleased(), 403);

        $validated = $request->validated();

        $this->ensureVaultHasNoOtherExecutor($vault, $validated['type'], $beneficiary);

        $beneficiary->update($validated);

        return redirect()
            ->route('vault.show', $vault)
            ->with('status', 'Beneficiary updated.');
    }

    public function destroy(
        PersonOfInterestVault $vault,
        PersonOfInterestVaultBeneficiary $beneficiary
    ): RedirectResponse {
        abort_unless($beneficiary->vault_id === $vault->id, 404);
        abort_unless($vault->isManagedBy(request()->user()), 403);
        abort_if($vault->isReleased(), 403);

        $beneficiary->delete();

        return redirect()
            ->route('vault.show', $vault)
            ->with('status', 'Beneficiary removed.');
    }

    private function ensureVaultHasNoOtherExecutor(
        PersonOfInterestVault $vault,
        string $type,
        ?PersonOfInterestVaultBeneficiary $ignoring = null
    ): void {
        if ($type !== 'executor') {
            return;
        }

        $executorExists = $vault->beneficiaries()
            ->where('type', 'executor')
            ->when($ignoring !== null, fn ($query) => $query->whereKeyNot($ignoring->id))
            ->exists();

        if ($executorExists) {
            throw ValidationException::withMessages([
                'type' => 'This vault already has an executor. Only one executor is allowed.',
            ]);
        }
    }

    private function generateAccessCode(): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $code = '';

        for ($i = 0; $i < 10; $i++) {
            $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }

        return $code;
    }

    private function accessCodeHint(string $plainCode): string
    {
        return substr($plainCode, 0, 2).'******'.substr($plainCode, -2);
    }
}
