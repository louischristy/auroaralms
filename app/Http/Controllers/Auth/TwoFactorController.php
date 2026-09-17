<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuditLogService;
use App\Services\TotpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TwoFactorController extends Controller
{
    public function __construct(
        private TotpService $totp,
    ) {}

    /**
     * Show the 2FA setup page with QR code.
     */
    public function setup()
    {
        $user = Auth::user();

        if ($user->two_factor_enabled) {
            return redirect()->route('profile.edit')
                ->with('info', 'Two-factor authentication is already enabled.');
        }

        $secret = $this->totp->generateSecret();
        session(['2fa_setup_secret' => $secret]);

        $qrUri = $this->totp->getQrUri($secret, $user->email);

        return view('auth.two-factor.setup', compact('secret', 'qrUri'));
    }

    /**
     * Confirm the TOTP code to enable 2FA.
     */
    public function confirmSetup(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $secret = session('2fa_setup_secret');

        if (!$secret) {
            return redirect()->route('two-factor.setup')
                ->with('error', 'Setup session expired. Please start again.');
        }

        if (!$this->totp->verify($secret, $request->code)) {
            return back()->withErrors(['code' => 'Invalid verification code. Please try again.']);
        }

        $user = Auth::user();
        $recoveryCodes = $this->totp->generateRecoveryCodes();

        $user->update([
            'two_factor_secret' => $secret,
            'two_factor_enabled' => true,
            'two_factor_recovery_codes' => $recoveryCodes,
        ]);

        session()->forget('2fa_setup_secret');
        session(['2fa:verified' => true]);

        AuditLogService::log('2fa_enabled', $user);

        return view('auth.two-factor.recovery-codes', compact('recoveryCodes'));
    }

    /**
     * Disable 2FA.
     */
    public function disable(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
        ]);

        $user = Auth::user();

        // Check if tenant requires 2FA
        if ($user->tenant && $user->tenant->require_two_factor) {
            return back()->with('error', 'Your organization requires two-factor authentication. Contact your administrator to change this policy.');
        }

        $user->update([
            'two_factor_secret' => null,
            'two_factor_enabled' => false,
            'two_factor_recovery_codes' => null,
        ]);

        AuditLogService::log('2fa_disabled', $user);

        return redirect()->route('profile.edit')
            ->with('success', 'Two-factor authentication has been disabled.');
    }

    /**
     * Show the 2FA challenge page (after login).
     */
    public function challenge()
    {
        if (!session('2fa:user_id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor.challenge');
    }

    /**
     * Verify the 2FA code during login.
     */
    public function verifyChallenge(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string'],
        ]);

        $userId = session('2fa:user_id');
        $remember = session('2fa:remember', false);

        if (!$userId) {
            return redirect()->route('login')
                ->with('error', 'Session expired. Please log in again.');
        }

        $user = \App\Models\User::find($userId);

        if (!$user) {
            session()->forget(['2fa:user_id', '2fa:remember']);
            return redirect()->route('login');
        }

        $code = trim($request->code);

        // Try TOTP code first
        if (strlen($code) === 6 && $this->totp->verify($user->two_factor_secret, $code)) {
            return $this->completeTwoFactorLogin($request, $user, $remember);
        }

        // Try recovery code
        $recoveryCodes = $user->two_factor_recovery_codes ?? [];
        $upperCode = strtoupper($code);

        if (in_array($upperCode, $recoveryCodes)) {
            // Remove used recovery code
            $user->update([
                'two_factor_recovery_codes' => array_values(array_diff($recoveryCodes, [$upperCode])),
            ]);

            AuditLogService::log('2fa_recovery_code_used', $user);

            return $this->completeTwoFactorLogin($request, $user, $remember);
        }

        return back()->withErrors(['code' => 'Invalid authentication code.']);
    }

    /**
     * Regenerate recovery codes.
     */
    public function regenerateRecoveryCodes(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
        ]);

        $user = Auth::user();

        if (!$user->two_factor_enabled) {
            return redirect()->route('profile.edit');
        }

        $recoveryCodes = $this->totp->generateRecoveryCodes();
        $user->update(['two_factor_recovery_codes' => $recoveryCodes]);

        AuditLogService::log('2fa_recovery_codes_regenerated', $user);

        return view('auth.two-factor.recovery-codes', compact('recoveryCodes'));
    }

    /**
     * Complete the 2FA login.
     */
    private function completeTwoFactorLogin(Request $request, $user, bool $remember): \Illuminate\Http\RedirectResponse
    {
        session()->forget(['2fa:user_id', '2fa:remember']);

        Auth::login($user, $remember);
        $request->session()->regenerate();
        session(['2fa:verified' => true]);

        $user->update(['last_login_at' => now()]);

        AuditLogService::log('login_success', $user, ['method' => '2fa']);

        return redirect()->intended(route('dashboard'));
    }
}
