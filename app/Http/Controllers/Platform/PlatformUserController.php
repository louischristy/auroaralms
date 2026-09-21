<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class PlatformUserController extends Controller
{
    public function index(Request $request)
    {
        $tenants = Tenant::orderBy('name')->get();

        $users = User::withoutTenantScope()
            ->with(['tenant', 'roles', 'department'])
            ->when($request->search, fn ($q, $s) => $q->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%");
            }))
            ->when($request->tenant_id, fn ($q, $t) => $q->where('tenant_id', $t))
            ->when($request->status !== null && $request->status !== '', fn ($q) => $q->where('is_active', request('status')))
            ->when($request->role, fn ($q, $r) => $q->role($r))
            ->whereNotNull('tenant_id') // exclude platform admin
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('platform.users.index', compact('users', 'tenants'));
    }

    public function resetPassword(int $user)
    {
        $user = User::withoutTenantScope()->findOrFail($user);

        if (!$user->tenant_id) {
            abort(403, 'Cannot reset platform admin password from here.');
        }

        Password::sendResetLink(['email' => $user->email]);

        return back()->with('success', "Password reset email sent to {$user->email}.");
    }
}
