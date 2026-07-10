<?php

namespace App\Services;

use App\Models\MemorialPagePamphlet;
use App\Models\PersonOfInterest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Cookie;

class GuestPamphletDraftService
{
    private const COOKIE_NAME = 'guest_pamphlet_token';

    public function issueTokenForPamphlet(MemorialPagePamphlet $pamphlet): string
    {
        $token = Str::random(64);
        $personOfInterest = $this->personOfInterestForPamphlet($pamphlet);

        $personOfInterest?->update([
            'guest_token_hash' => Hash::make($token),
            'guest_token_expires_at' => now()->addDays(7),
        ]);

        return $token;
    }

    public function cookieFromToken(string $token): Cookie
    {
        return cookie(
            self::COOKIE_NAME,
            $token,
            60 * 24 * 7,
            null,
            null,
            true,
            true,
            false,
            'lax'
        );
    }

    public function clearCookie(): Cookie
    {
        return cookie()->forget(self::COOKIE_NAME);
    }

    public function requestOwnsPamphlet(Request $request, MemorialPagePamphlet $pamphlet): bool
    {
        $owner = $pamphlet->owner();

        if ($request->user() !== null && $owner !== null && $owner->is($request->user())) {
            return true;
        }

        $personOfInterest = $this->personOfInterestForPamphlet($pamphlet);

        if ($personOfInterest === null) {
            return false;
        }

        $token = (string) $request->cookie(self::COOKIE_NAME, '');

        if ($token === '' || blank($personOfInterest->guest_token_hash)) {
            return false;
        }

        if ($personOfInterest->guest_token_expires_at !== null && $personOfInterest->guest_token_expires_at->isPast()) {
            return false;
        }

        return Hash::check($token, $personOfInterest->guest_token_hash);
    }

    public function claimPamphletToUser(MemorialPagePamphlet $pamphlet, User $user): void
    {
        $personOfInterest = $this->personOfInterestForPamphlet($pamphlet);

        if ($personOfInterest === null) {
            return;
        }

        $personOfInterest->update([
            'created_by_user_id' => $user->id,
            'guest_token_hash' => null,
            'guest_token_expires_at' => null,
        ]);

        $personOfInterest->users()->syncWithoutDetaching([
            $user->id => ['role' => 'owner'],
        ]);
    }

    private function personOfInterestForPamphlet(MemorialPagePamphlet $pamphlet): ?PersonOfInterest
    {
        return $pamphlet->memorialPage?->personOfInterest;
    }
}
