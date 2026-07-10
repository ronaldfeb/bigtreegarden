<?php

namespace App\Http\Controllers\Vault;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vault\StoreVaultMediaRequest;
use App\Models\PersonOfInterestVault;
use App\Models\PersonOfInterestVaultMedia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class VaultMediaController extends Controller
{
    public function store(
        StoreVaultMediaRequest $request,
        PersonOfInterestVault $vault
    ): RedirectResponse {
        abort_unless($vault->isManagedBy($request->user()), 403);
        abort_if($vault->isReleased(), 403);

        $validated = $request->validated();
        $file = $request->file('file');

        if ($vault->usedStorageBytes() + $file->getSize() > $vault->storageLimitBytes()) {
            throw ValidationException::withMessages([
                'file' => 'Uploading this file would exceed the vault storage limit of '
                    ."{$vault->storage_limit_mb} MB.",
            ]);
        }

        $filePath = $file->store("vaults/{$vault->id}", 'public');

        $vault->media()->create([
            'uploaded_by_user_id' => $request->user()->id,
            'type' => $this->mediaTypeForMime($file->getMimeType() ?? ''),
            'title' => $validated['title'] ?? $file->getClientOriginalName(),
            'file_path' => $filePath,
            'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
            'file_size_bytes' => $file->getSize(),
        ]);

        return redirect()
            ->route('vault.show', $vault)
            ->with('status', 'File uploaded to the vault.');
    }

    public function destroy(
        PersonOfInterestVault $vault,
        PersonOfInterestVaultMedia $media
    ): RedirectResponse {
        abort_unless($media->vault_id === $vault->id, 404);
        abort_unless($vault->isManagedBy(request()->user()), 403);
        abort_if($vault->isReleased(), 403);

        Storage::disk('public')->delete($media->file_path);
        $media->delete();

        return redirect()
            ->route('vault.show', $vault)
            ->with('status', 'File removed from the vault.');
    }

    private function mediaTypeForMime(string $mimeType): string
    {
        return match (true) {
            str_starts_with($mimeType, 'image/') => 'image',
            str_starts_with($mimeType, 'video/') => 'video',
            str_starts_with($mimeType, 'audio/') => 'voice_note',
            default => 'pdf',
        };
    }
}
