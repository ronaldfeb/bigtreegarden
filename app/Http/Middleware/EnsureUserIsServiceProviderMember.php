<?php

namespace App\Http\Middleware;

use App\Models\ServiceProviderUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsServiceProviderMember
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            abort(403);
        }

        $membership = ServiceProviderUser::query()
            ->where('user_id', $user->id)
            ->first();

        if ($membership === null) {
            abort(403);
        }

        $request->attributes->set('serviceProviderMembership', $membership);

        return $next($request);
    }
}
