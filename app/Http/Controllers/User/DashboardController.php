<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Pamphlet;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $pamphlets = Pamphlet::query()
            ->with('background')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get()
            ->map(fn (Pamphlet $pamphlet): array => [
                'id' => $pamphlet->id,
                'heading' => $pamphlet->heading,
                'person_full_name' => $pamphlet->person_full_name,
                'status' => $pamphlet->status,
                'date_of_birth' => optional($pamphlet->date_of_birth)->toDateString(),
                'date_of_passing' => optional($pamphlet->date_of_passing)->toDateString(),
                'background_asset_path' => $pamphlet->background?->asset_path,
            ]);

        return Inertia::render('user/Dashboard', [
            'pamphlets' => $pamphlets,
        ]);
    }
}
