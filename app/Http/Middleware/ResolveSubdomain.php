<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves a tenant from the request subdomain (e.g. acme.auroara.com).
 * Falls through silently when the host is not a tenant subdomain.
 */
class ResolveSubdomain
{
    public const RESERVED = ['www', 'api', 'admin', 'app', 'mail', 'ftp'];

    public function handle(Request $request, Closure $next): Response
    {
        $subdomain = $this->extractSubdomain($request->getHost());

        if ($subdomain && !in_array($subdomain, self::RESERVED, true)) {
            $tenant = Tenant::active()->where('subdomain', $subdomain)->first();

            if ($tenant) {
                app()->instance('subdomain_tenant', $tenant);
                app()->instance('current_tenant', $tenant);
                view()->share('currentTenant', $tenant);
            }
        }

        return $next($request);
    }

    private function extractSubdomain(string $host): ?string
    {
        $base = config('app.base_domain') ?: parse_url((string) config('app.url'), PHP_URL_HOST);
        $host = strtolower($host);

        if (!$base) {
            return null;
        }
        $base = strtolower(preg_replace('/^www\./', '', $base));

        if ($host === $base || !str_ends_with($host, '.' . $base)) {
            return null;
        }

        $sub = substr($host, 0, -strlen('.' . $base));

        // Only single-level subdomains are tenant subdomains.
        return ($sub !== '' && !str_contains($sub, '.')) ? $sub : null;
    }
}
