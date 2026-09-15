<?php

namespace App\Http\Middleware;

use App\Models\PlatformSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Injects branding variables (colors, logo, name) into views.
 * Resolves: tenant branding → platform branding → Auroara defaults.
 */
class InjectBranding
{
    private array $defaults = [
        'platform_name' => 'Auroara LMS',
        'company_name' => 'Auroara Technologies Sdn Bhd',
        'primary_color' => '#2B4C7E',
        'secondary_color' => '#3A7BD5',
        'accent_color' => '#5BC0EB',
        'neutral_color' => '#A8A9AD',
        'logo_path' => '/images/auroara-logo.png',
        'favicon_path' => '/images/favicon.ico',
        'show_powered_by' => true,
        'powered_by_text' => 'Powered by Auroara Technologies',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $branding = $this->defaults;

        // Layer 1: Platform-level overrides
        $platformBrand = PlatformSetting::getGroup('brand');
        if (!empty($platformBrand)) {
            $branding = array_merge($branding, $platformBrand);
        }

        // Layer 2: Tenant-level overrides (if tenant context exists)
        $tenant = app()->bound('current_tenant') ? app('current_tenant') : null;
        if ($tenant) {
            $tenantBrand = $tenant->getBrandSettings();
            // Only override non-null tenant values
            foreach ($tenantBrand as $key => $value) {
                if ($value !== null) {
                    $branding[$key] = $value;
                }
            }
            // Tenant logo takes priority
            if ($tenant->logo_path) {
                $branding['logo_path'] = '/uploads/tenants/' . $tenant->id . '/' . $tenant->logo_path;
            }
        }

        view()->share('branding', $branding);

        return $next($request);
    }
}
