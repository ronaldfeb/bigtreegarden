<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasActiveSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || ! $user->hasActiveSubscription()) {
            return redirect()
                ->route('pricing')
                ->with('status', 'An active subscription is required to use the vault.');
        }

        return $next($request);
    }
}
