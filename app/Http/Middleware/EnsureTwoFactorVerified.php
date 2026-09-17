<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorVerified
{
    /**
     * If the user has 2FA enabled but hasn't verified this session,
     * redirect to the challenge page.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        // If user has 2FA enabled, check they've completed the challenge
        if ($user->two_factor_enabled && !session('2fa:verified')) {
            return redirect()->route('two-factor.challenge');
        }

        // If tenant requires 2FA but user hasn't set it up, redirect to setup
        if ($user->tenant
            && $user->tenant->require_two_factor
            && !$user->two_factor_enabled
            && !$request->routeIs('two-factor.*', 'logout')
        ) {
            return redirect()->route('two-factor.setup')
                ->with('warning', 'Your organization requires two-factor authentication. Please set it up to continue.');
        }

        return $next($request);
    }
}
