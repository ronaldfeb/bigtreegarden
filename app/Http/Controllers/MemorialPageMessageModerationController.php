<?php

namespace App\Http\Controllers;

use App\Models\MemorialPageMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MemorialPageMessageModerationController extends Controller
{
    public function approve(Request $request, MemorialPageMessage $message): RedirectResponse
    {
        $this->authorizeModeration($request, $message);

        $message->update([
            'status' => 'approved',
            'approved_by_user_id' => $request->user()->id,
            'approved_at' => now(),
        ]);

        return back()->with('status', 'Message approved.');
    }

    public function reject(Request $request, MemorialPageMessage $message): RedirectResponse
    {
        $this->authorizeModeration($request, $message);

        $message->update(['status' => 'rejected']);

        return back()->with('status', 'Message rejected.');
    }

    /**
     * Only active staff members or users linked to the memorial page's
     * person of interest may moderate a message.
     */
    private function authorizeModeration(Request $request, MemorialPageMessage $message): void
    {
        $user = $request->user();

        $isActiveStaff = $user->staffUser !== null && $user->staffUser->is_active;

        $isLinkedToPersonOfInterest = $message->memorialPage
            ->personOfInterest
            ->users()
            ->where('users.id', $user->id)
            ->exists();

        abort_unless($isActiveStaff || $isLinkedToPersonOfInterest, 403);
    }
}
