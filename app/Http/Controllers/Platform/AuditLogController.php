<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = AuditLog::with('user')
            ->orderByDesc('created_at');

        // Client admins see only their tenant's logs
        if (!$user->isPlatformAdmin()) {
            $query->where('tenant_id', $user->tenant_id);
        }

        // Filters
        if ($request->filled('action')) {
            $query->where('action', 'like', '%' . $request->action . '%');
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Get distinct actions for the filter dropdown
        $actionsQuery = AuditLog::select('action')->distinct()->orderBy('action');
        if (!$user->isPlatformAdmin()) {
            $actionsQuery->where('tenant_id', $user->tenant_id);
        }
        $actions = $actionsQuery->pluck('action');

        $logs = $query->paginate(25)->withQueryString();

        return view('platform.audit-logs.index', [
            'logs' => $logs,
            'actions' => $actions,
            'filters' => $request->only(['action', 'user_id', 'date_from', 'date_to', 'search']),
        ]);
    }

    public function show(AuditLog $auditLog)
    {
        $user = Auth::user();

        // Ensure client admins can only see their tenant's logs
        if (!$user->isPlatformAdmin() && $auditLog->tenant_id !== $user->tenant_id) {
            abort(403);
        }

        $auditLog->load('user');

        return view('platform.audit-logs.show', [
            'log' => $auditLog,
        ]);
    }
}
