<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantSsoConfig;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SsoController extends Controller
{
    /**
     * Redirect to the SSO provider.
     */
    public function redirect(Request $request, string $provider)
    {
        $tenantId = $request->query('tenant');
        if (! $tenantId) {
            return redirect()->route('login')->with('error', 'Tenant not specified for SSO.');
        }

        $config = TenantSsoConfig::where('tenant_id', $tenantId)
            ->where('provider', $provider)
            ->where('is_active', true)
            ->first();

        if (! $config) {
            return redirect()->route('login')->with('error', 'SSO is not configured for this organization.');
        }

        // Store tenant ID in session for the callback
        session(['sso_tenant_id' => $tenantId, 'sso_provider' => $provider]);

        // Build Socialite with tenant-specific credentials
        $driver = $this->buildDriver($config);

        return $driver->redirect();
    }

    /**
     * Handle the callback from the SSO provider.
     */
    public function callback(Request $request, string $provider)
    {
        $tenantId = session('sso_tenant_id');
        $sessionProvider = session('sso_provider');

        if (! $tenantId || $sessionProvider !== $provider) {
            return redirect()->route('login')->with('error', 'Invalid SSO session. Please try again.');
        }

        $config = TenantSsoConfig::where('tenant_id', $tenantId)
            ->where('provider', $provider)
            ->where('is_active', true)
            ->first();

        if (! $config) {
            return redirect()->route('login')->with('error', 'SSO configuration not found.');
        }

        try {
            $driver = $this->buildDriver($config);
            $socialUser = $driver->user();
        } catch (\Throwable $e) {
            Log::error("SSO callback error [{$provider}]: " . $e->getMessage());
            return redirect()->route('login')->with('error', 'SSO authentication failed. Please try again.');
        }

        // Validate email domain
        if (! $config->isDomainAllowed($socialUser->getEmail())) {
            return redirect()->route('login')
                ->with('error', 'Your email domain is not authorized for this organization.');
        }

        // Find or create user
        $user = $this->findOrCreateUser($socialUser, $config);

        if (! $user) {
            return redirect()->route('login')
                ->with('error', 'Unable to sign in. Contact your administrator.');
        }

        // Clean up session
        session()->forget(['sso_tenant_id', 'sso_provider']);

        Auth::login($user, true);

        return redirect()->route('dashboard');
    }

    /**
     * Find existing user by email+tenant, or auto-provision if enabled.
     */
    private function findOrCreateUser($socialUser, TenantSsoConfig $config): ?User
    {
        $email = strtolower($socialUser->getEmail());

        // Look for existing user in this tenant
        $user = User::withoutGlobalScopes()
            ->where('email', $email)
            ->where('tenant_id', $config->tenant_id)
            ->first();

        if ($user) {
            // Update name/avatar if changed
            $user->update([
                'name' => $socialUser->getName() ?: $user->name,
            ]);
            return $user;
        }

        // Auto-provision new user if enabled
        if (! $config->auto_provision) {
            return null;
        }

        $user = User::create([
            'name'      => $socialUser->getName() ?: explode('@', $email)[0],
            'email'     => $email,
            'password'  => bcrypt(Str::random(32)), // random password (won't be used with SSO)
            'tenant_id' => $config->tenant_id,
        ]);

        // Assign default employee role
        if (method_exists($user, 'assignRole')) {
            $user->assignRole('employee');
        }

        return $user;
    }

    /**
     * Build a Socialite driver with tenant-specific OAuth credentials.
     */
    private function buildDriver(TenantSsoConfig $config)
    {
        $redirectUrl = route('sso.callback', ['provider' => $config->provider]);

        // Dynamically set the config for this provider
        $key = "services.{$config->provider}";
        config([
            "{$key}.client_id"     => $config->client_id,
            "{$key}.client_secret" => $config->client_secret,
            "{$key}.redirect"      => $redirectUrl,
        ]);

        // Microsoft needs the tenant identifier for single-tenant apps
        if ($config->provider === 'microsoft' && $config->tenant_identifier) {
            config(["{$key}.tenant" => $config->tenant_identifier]);
        }

        return Socialite::driver($config->provider);
    }
}
