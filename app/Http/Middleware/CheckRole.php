<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Check if the authenticated user has any of the specified roles.
 * Usage: ->middleware('role:platform-admin,client-admin')
 */
class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (!$user->hasAnyRole($roles)) {
            abort(403, 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}
