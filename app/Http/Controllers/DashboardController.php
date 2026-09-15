<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->isPlatformAdmin()) {
            return $this->platformDashboard();
        }

        if ($user->isClientAdmin()) {
            return $this->clientDashboard($user);
        }

        if ($user->isManager()) {
            return $this->managerDashboard($user);
        }

        return $this->employeeDashboard($user);
    }

    private function platformDashboard()
    {
        // Stats across all tenants
        return view('dashboard.platform', [
            'stats' => [
                'total_tenants' => \App\Models\Tenant::count(),
                'total_users' => \App\Models\User::withoutTenantScope()->whereNotNull('tenant_id')->count(),
                'active_tenants' => \App\Models\Tenant::where('is_active', true)->count(),
            ],
        ]);
    }

    private function clientDashboard($user)
    {
        return view('dashboard.client', [
            'stats' => [
                'total_users' => \App\Models\User::count(),
                'total_departments' => \App\Models\Department::count(),
            ],
        ]);
    }

    private function managerDashboard($user)
    {
        return view('dashboard.manager', [
            'stats' => [
                'team_size' => \App\Models\User::where('department_id', $user->department_id)->count(),
            ],
        ]);
    }

    private function employeeDashboard($user)
    {
        return view('dashboard.employee', [
            'user' => $user,
        ]);
    }

    public function teamReports(Request $request)
    {
        return view('reports.team');
    }
}
