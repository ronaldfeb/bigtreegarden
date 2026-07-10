<?php

namespace App\Http\Controllers\Staff\Directory;

use App\Http\Controllers\Staff\Controller;
use App\Models\PersonOfInterestVault;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class VaultController extends Controller
{
    /**
     * Read-only oversight of vaults: aggregate stats only, no content access.
     */
    public function index(): Response
    {
        Gate::authorize('manage-directory');

        $vaults = PersonOfInterestVault::query()
            ->with('personOfInterest')
            ->withCount(['beneficiaries', 'media', 'posts'])
            ->withSum('media', 'file_size_bytes')
            ->latest()
            ->paginate(20)
            ->through(fn (PersonOfInterestVault $vault): array => [
                'id' => $vault->id,
                'person_of_interest_name' => $vault->personOfInterest?->display_name,
                'status' => $vault->status,
                'beneficiaries_count' => $vault->beneficiaries_count,
                'media_count' => $vault->media_count,
                'posts_count' => $vault->posts_count,
                'storage_used_mb' => round(((int) $vault->media_sum_file_size_bytes) / 1048576, 2),
                'released_at' => $vault->released_at?->toDateTimeString(),
            ]);

        return Inertia::render('staff/directory/vaults/Index', [
            'vaults' => $vaults,
        ]);
    }
}
