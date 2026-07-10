<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsStaff
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $staffUser = $request->user()?->staffUser;

        if ($staffUser === null || ! $staffUser->is_active) {
            abort(403);
        }

        return $next($request);
    }
}
