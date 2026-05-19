<?php

namespace App\Services;

use App\Models\Pamphlet;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Cookie;

class GuestPamphletDraftService
{
    private const COOKIE_NAME = 'guest_pamphlet_token';

    public function issueTokenForPamphlet(Pamphlet $pamphlet): string
    {
        $token = Str::random(64);

        $pamphlet->update([
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

    public function requestOwnsPamphlet(Request $request, Pamphlet $pamphlet): bool
    {
        if ($request->user() !== null && $pamphlet->user_id === $request->user()->id) {
            return true;
        }

        $token = (string) $request->cookie(self::COOKIE_NAME, '');

        if ($token === '' || blank($pamphlet->guest_token_hash)) {
            return false;
        }

        if ($pamphlet->guest_token_expires_at !== null && $pamphlet->guest_token_expires_at->isPast()) {
            return false;
        }

        return Hash::check($token, $pamphlet->guest_token_hash);
    }

    public function claimPamphletToUser(Pamphlet $pamphlet, User $user): void
    {
        $pamphlet->update([
            'user_id' => $user->id,
            'guest_token_hash' => null,
            'guest_token_expires_at' => null,
        ]);
    }
}
