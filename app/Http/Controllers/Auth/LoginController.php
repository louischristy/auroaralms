<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
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
