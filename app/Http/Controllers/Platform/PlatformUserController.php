<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class PlatformUserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::withoutTenantScope()
            ->with('tenant')
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%"))
            ->when($request->tenant_id, fn ($q, $t) => $q->where('tenant_id', $t))
            ->latest()
            ->paginate(20);

        return view('platform.users.index', compact('users'));
    }
}
