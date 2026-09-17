<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SessionController extends Controller
{
    /**
     * List the user's active sessions.
     */
    public function index(Request $request)
    {
        $sessions = $this->getActiveSessions($request);

        return view('profile.sessions', [
            'sessions' => $sessions,
            'currentSessionId' => $request->session()->getId(),
        ]);
    }

    /**
     * Revoke a specific session (log out that device).
     */
    public function destroy(Request $request, string $sessionId)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
        ]);

        if ($sessionId === $request->session()->getId()) {
            return back()->with('error', 'Use the logout button to end your current session.');
        }

        $deleted = DB::table('sessions')
            ->where('id', $sessionId)
            ->where('user_id', Auth::id())
            ->delete();

        if ($deleted) {
            return back()->with('success', 'Session revoked successfully.');
        }

        return back()->with('error', 'Session not found.');
    }

    /**
     * Revoke all sessions except the current one.
     */
    public function destroyAll(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
        ]);

        DB::table('sessions')
            ->where('user_id', Auth::id())
            ->where('id', '!=', $request->session()->getId())
            ->delete();

        return back()->with('success', 'All other sessions have been revoked.');
    }

    /**
     * Force logout a specific user (platform/client admin only).
     */
    public function forceLogout(Request $request, int $userId)
    {
        $user = Auth::user();

        if ($user->isPlatformAdmin()) {
            // allowed
        } elseif ($user->isClientAdmin()) {
            $target = \App\Models\User::where('id', $userId)
                ->where('tenant_id', $user->tenant_id)
                ->first();

            if (!$target) {
                return back()->with('error', 'User not found.');
            }
        } else {
            abort(403);
        }

        $count = DB::table('sessions')
            ->where('user_id', $userId)
            ->delete();

        return back()->with('success', "User logged out from {$count} session(s).");
    }

    /**
     * Get all active sessions for the current user, parsed from user agent.
     */
    private function getActiveSessions(Request $request): array
    {
        $sessions = DB::table('sessions')
            ->where('user_id', Auth::id())
            ->orderByDesc('last_activity')
            ->get();

        return $sessions->map(function ($session) use ($request) {
            $ua = $session->user_agent ?? '';

            return (object) [
                'id' => $session->id,
                'ip_address' => $session->ip_address,
                'is_current' => $session->id === $request->session()->getId(),
                'browser' => $this->parseBrowser($ua),
                'platform' => $this->parsePlatform($ua),
                'device_type' => $this->parseDeviceType($ua),
                'last_active' => Carbon::createFromTimestamp($session->last_activity),
            ];
        })->toArray();
    }

    private function parseBrowser(string $ua): string
    {
        if (preg_match('/Edg[e\/]/', $ua)) return 'Edge';
        if (preg_match('/OPR|Opera/', $ua)) return 'Opera';
        if (preg_match('/Chrome/', $ua)) return 'Chrome';
        if (preg_match('/Safari/', $ua)) return 'Safari';
        if (preg_match('/Firefox/', $ua)) return 'Firefox';

        return 'Unknown';
    }

    private function parsePlatform(string $ua): string
    {
        if (preg_match('/Windows/', $ua)) return 'Windows';
        if (preg_match('/Macintosh|Mac OS/', $ua)) return 'macOS';
        if (preg_match('/Linux/', $ua) && !preg_match('/Android/', $ua)) return 'Linux';
        if (preg_match('/Android/', $ua)) return 'Android';
        if (preg_match('/iPhone|iPad|iPod/', $ua)) return 'iOS';

        return 'Unknown';
    }

    private function parseDeviceType(string $ua): string
    {
        if (preg_match('/Mobile|Android.*Mobile|iPhone/', $ua)) return 'mobile';
        if (preg_match('/iPad|Tablet/', $ua)) return 'tablet';

        return 'desktop';
    }
}
