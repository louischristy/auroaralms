<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;

class PlatformUserController extends Controller
{
    public function index(Request $request)
    {
        $tenants = Tenant::orderBy('name')->get();

        $users = User::withoutTenantScope()
            ->with('tenant')
            ->when($request->search, fn ($q, $s) => $q->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%");
            }))
            ->when($request->tenant_id, fn ($q, $t) => $q->where('tenant_id', $t))
            ->when($request->status !== null && $request->status !== '', fn ($q) => $q->where('is_active', request('status')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('platform.users.index', compact('users', 'tenants'));
    }
}
