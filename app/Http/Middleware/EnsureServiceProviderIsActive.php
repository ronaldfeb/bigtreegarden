<?php

namespace App\Http\Middleware;

use App\Models\ServiceProviderUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureServiceProviderIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var ServiceProviderUser|null $membership */
        $membership = $request->attributes->get('serviceProviderMembership');

        if ($membership === null) {
            abort(403);
        }

        $serviceProvider = $membership->serviceProvider;

        if ($serviceProvider === null || ! $serviceProvider->isActive()) {
            abort(403, 'Your service provider listing must be active to access this feature.');
        }

        return $next($request);
    }
}
