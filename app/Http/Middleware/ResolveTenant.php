<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the current tenant from the authenticated user
 * and binds it to the app container for global scope usage.
 */
class ResolveTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->tenant_id) {
            // Bind tenant ID for global scopes
            app()->instance('current_tenant_id', $user->tenant_id);

            // Load tenant model and share with views
            $tenant = $user->tenant;
            app()->instance('current_tenant', $tenant);
            view()->share('currentTenant', $tenant);
        } elseif ($user && $user->isPlatformAdmin()) {
            // Platform admins see all data (no tenant scoping)
            app()->instance('current_tenant_id', null);
            app()->instance('current_tenant', null);
            view()->share('currentTenant', null);
        }

        return $next($request);
    }
}
