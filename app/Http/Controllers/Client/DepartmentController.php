<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->hasRole('platform-admin')) {
            $tenants = Tenant::orderBy('name')->get();

            $query = Department::withoutTenantScope()
                ->withCount('users')
                ->with(['tenant', 'manager']);

            if ($request->tenant_id) {
                $query->where('tenant_id', $request->tenant_id);
            }

            if ($request->search) {
                $query->where('name', 'like', "%{$request->search}%");
            }

            $departments = $query->latest()->paginate(20)->withQueryString();

            return view('client.departments.index', compact('departments', 'tenants'));
        }

        // Client admin / manager sees only their tenant's departments
        $departments = Department::withCount('users')
            ->with('manager')
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('client.departments.index', compact('departments'));
    }

    public function create()
    {
        $user = Auth::user();
        $tenants = null;

        if ($user->hasRole('platform-admin')) {
            $tenants = Tenant::orderBy('name')->get();
            $managers = User::withoutTenantScope()->role('manager')->orderBy('name')->get();
        } else {
            $managers = User::role('manager')->orderBy('name')->get();
        }

        return view('client.departments.create', compact('managers', 'tenants'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'manager_id' => ['nullable', 'exists:users,id'],
            'tenant_id' => ['nullable', 'exists:tenants,id'],
        ]);

        $user = Auth::user();

        if ($user->hasRole('platform-admin') && !empty($validated['tenant_id'])) {
            $validated['tenant_id'] = $validated['tenant_id'];
        } else {
            $validated['tenant_id'] = $user->tenant_id;
        }

        Department::withoutTenantScope()->create($validated);

        return redirect()->route('manage.departments.index')
            ->with('success', 'Department created successfully.');
    }

    public function show(Department $department)
    {
        if (Auth::user()->hasRole('platform-admin')) {
            $department = Department::withoutTenantScope()->with(['users', 'manager', 'tenant'])->findOrFail($department->id);
        } else {
            $department->load(['users', 'manager']);
        }

        return view('client.departments.show', compact('department'));
    }

    public function edit(Department $department)
    {
        $tenants = null;

        if (Auth::user()->hasRole('platform-admin')) {
            $department = Department::withoutTenantScope()->findOrFail($department->id);
            $tenants = Tenant::orderBy('name')->get();
            $managers = User::withoutTenantScope()->role('manager')->orderBy('name')->get();
        } else {
            $managers = User::role('manager')->orderBy('name')->get();
        }

        return view('client.departments.edit', compact('department', 'managers', 'tenants'));
    }

    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'manager_id' => ['nullable', 'exists:users,id'],
            'tenant_id' => ['nullable', 'exists:tenants,id'],
        ]);

        if (Auth::user()->hasRole('platform-admin')) {
            $department = Department::withoutTenantScope()->findOrFail($department->id);
        }

        $department->update($validated);

        return redirect()->route('manage.departments.show', $department)
            ->with('success', 'Department updated successfully.');
    }

    public function destroy(Department $department)
    {
        if (Auth::user()->hasRole('platform-admin')) {
            $department = Department::withoutTenantScope()->findOrFail($department->id);
        }

        $department->delete();

        return redirect()->route('manage.departments.index')
            ->with('success', 'Department deleted successfully.');
    }
}
