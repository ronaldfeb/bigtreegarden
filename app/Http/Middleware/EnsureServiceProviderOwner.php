<?php

namespace App\Http\Middleware;

use App\Models\ServiceProviderUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureServiceProviderOwner
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var ServiceProviderUser|null $membership */
        $membership = $request->attributes->get('serviceProviderMembership');

        if ($membership === null || ! $membership->isOwner()) {
            abort(403, 'Only service provider owners can access this feature.');
        }

        return $next($request);
    }
}
