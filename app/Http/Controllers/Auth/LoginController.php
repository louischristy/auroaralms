<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\TenantSsoConfig;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Check if SSO is available for a given email domain (AJAX).
     */
    public function checkSso(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // Rate limit: 10 per minute per IP
        $key = 'sso-check|' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 10)) {
            return response()->json(['error' => 'Too many requests.'], 429);
        }
        RateLimiter::hit($key, 60);

        $domain = strtolower(substr(strrchr($request->email, '@'), 1));

        $configs = TenantSsoConfig::where('is_active', true)
            ->get()
            ->filter(fn($c) => $c->isDomainAllowed($request->email));

        if ($configs->isEmpty()) {
            return response()->json(['sso' => false]);
        }

        $config = $configs->first();
        $providers = $configs->map(fn($c) => [
            'provider'  => $c->provider,
            'label'     => TenantSsoConfig::providers()[$c->provider] ?? ucfirst($c->provider),
            'url'       => route('sso.redirect', ['provider' => $c->provider, 'tenant' => $c->tenant_id]),
            'force_sso' => $c->force_sso,
        ])->values();

        return response()->json([
            'sso'       => true,
            'force_sso' => $configs->contains('force_sso', true),
            'providers' => $providers,
        ]);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $throttleKey = Str::transliterate(Str::lower($credentials['email']) . '|' . $request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'email' => __('Too many login attempts. Please try again in :seconds seconds.', ['seconds' => $seconds]),
            ]);
        }

        // Block password login if tenant has force_sso enabled
        $domain = strtolower(substr(strrchr($credentials['email'], '@'), 1));
        $forceSso = TenantSsoConfig::where('is_active', true)
            ->where('force_sso', true)
            ->get()
            ->first(fn($c) => $c->isDomainAllowed($credentials['email']));

        if ($forceSso) {
            throw ValidationException::withMessages([
                'email' => __('Your organization requires SSO login. Please use the SSO button.'),
            ]);
        }

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($throttleKey, 60);

            AuditLogService::log('login_failed', null, [
                'email' => $credentials['email'],
            ]);

            throw ValidationException::withMessages([
                'email' => __('These credentials do not match our records.'),
            ]);
        }

        $user = Auth::user();

        if (! $user->is_active) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => __('Your account has been deactivated. Please contact your administrator.'),
            ]);
        }

        // Check tenant is active
        if ($user->tenant && ! $user->tenant->is_active) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => __('Your organization\'s account is currently inactive.'),
            ]);
        }

        RateLimiter::clear($throttleKey);

        // If 2FA is enabled, log out and redirect to challenge
        if ($user->two_factor_enabled) {
            $userId = $user->id;
            Auth::logout();

            $request->session()->put('2fa:user_id', $userId);
            $request->session()->put('2fa:remember', $request->boolean('remember'));

            return redirect()->route('two-factor.challenge');
        }

        $user->update(['last_login_at' => now()]);
        $request->session()->regenerate();

        AuditLogService::log('login_success', $user);

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        $user = Auth::user();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        AuditLogService::log('logout', $user);

        return redirect()->route('login');
    }
}
