<?php

namespace App\Http\Controllers;

use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class SecuritySettingsController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        return view('settings.security', [
            'user' => $user,
            'twoFactorEnabled' => (bool) $user->two_factor_enabled,
            'recoveryCodes' => $user->two_factor_recovery_codes ?? [],
            'tenantRequires2fa' => $user->tenant && $user->tenant->require_two_factor,
        ]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
        ]);

        $user = Auth::user();
        $user->update([
            'password' => Hash::make($request->password),
            'must_change_password' => false,
        ]);

        AuditLogService::log('password_changed', $user);

        return back()->with('success', 'Password changed successfully.');
    }
}
