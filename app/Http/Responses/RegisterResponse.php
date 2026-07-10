<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Symfony\Component\HttpFoundation\Response;

class RegisterResponse implements RegisterResponseContract
{
    /**
     * @param  Request  $request
     */
    public function toResponse($request): Response
    {
        if ($request->wantsJson()) {
            return new JsonResponse('', 201);
        }

        $intent = $request->input('intent');
        $hasIntendedUrl = $request->session()->has('url.intended');
        $response = redirect()->intended($this->defaultRedirectUrl($intent));

        if (! $hasIntendedUrl && $intent === 'vault') {
            $response->with('status', 'Choose a vault plan below to unlock your digital vault.');
        }

        return $response;
    }

    private function defaultRedirectUrl(mixed $intent): string
    {
        return match ($intent) {
            'pamphlet' => route('pamphlets.create'),
            'vault' => route('pricing'),
            'live' => route('memorial.find'),
            default => route('dashboard'),
        };
    }
}
