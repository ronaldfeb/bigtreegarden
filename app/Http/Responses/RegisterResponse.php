<?php

namespace App\Http\Responses;

use App\Enums\RegistrationIntent;
use App\Support\MemorialPackageSession;
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

        $intent = RegistrationIntent::tryFromInput($request->input('intent'));

        if ($intent?->onceOffPackageSlug() !== null) {
            MemorialPackageSession::put($intent->onceOffPackageSlug());
        }

        return redirect()->intended($this->defaultRedirectUrl($intent));
    }

    private function defaultRedirectUrl(?RegistrationIntent $intent): string
    {
        return match ($intent) {
            RegistrationIntent::FuneralMemorial, RegistrationIntent::MemorialLegacy => route('pamphlets.create'),
            RegistrationIntent::LivingLegacy => route('subscriptions.start'),
            RegistrationIntent::Live => route('memorial.find'),
            default => route('dashboard'),
        };
    }
}
