<?php

namespace App\Services;

use App\Models\User;

class IntercomJwtService
{
    public function isConfigured(): bool
    {
        $secret = config('services.intercom.messenger_secret');

        return is_string($secret) && $secret !== '';
    }

    public function forUser(?User $user): ?string
    {
        if ($user === null || ! $this->isConfigured()) {
            return null;
        }

        $payload = [
            'user_id' => (string) $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'created_at' => $user->created_at?->getTimestamp(),
            'exp' => now()->addSeconds($this->tokenTtl())->getTimestamp(),
        ];

        return $this->encode($payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function encode(array $payload): string
    {
        $header = $this->base64UrlEncode(json_encode([
            'typ' => 'JWT',
            'alg' => 'HS256',
        ], JSON_THROW_ON_ERROR));

        $encodedPayload = $this->base64UrlEncode(json_encode($payload, JSON_THROW_ON_ERROR));

        $signature = $this->base64UrlEncode(hash_hmac(
            'sha256',
            $header.'.'.$encodedPayload,
            (string) config('services.intercom.messenger_secret'),
            true,
        ));

        return $header.'.'.$encodedPayload.'.'.$signature;
    }

    private function tokenTtl(): int
    {
        $configuredTtl = config('services.intercom.jwt_ttl');

        if (is_int($configuredTtl) && $configuredTtl > 0) {
            return $configuredTtl;
        }

        if (is_numeric($configuredTtl) && (int) $configuredTtl > 0) {
            return (int) $configuredTtl;
        }

        return (int) config('session.lifetime', 120) * 60;
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
