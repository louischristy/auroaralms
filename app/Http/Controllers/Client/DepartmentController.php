<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::withCount('users')->latest()->paginate(15);
        return view('client.departments.index', compact('departments'));
    }

    public function create()
    {
        $managers = User::role('manager')->orderBy('name')->get();
        return view('client.departments.create', compact('managers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'manager_id' => ['nullable', 'exists:users,id'],
        ]);

        $validated['tenant_id'] = Auth::user()->tenant_id;

        Department::create($validated);

        return redirect()->route('manage.departments.index')
            ->with('success', 'Department created successfully.');
    }

    public function show(Department $department)
    {
        $department->load(['users', 'manager']);
        return view('client.departments.show', compact('department'));
    }

    public function edit(Department $department)
    {
        $managers = User::role('manager')->orderBy('name')->get();
        return view('client.departments.edit', compact('department', 'managers'));
    }

    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'manager_id' => ['nullable', 'exists:users,id'],
        ]);

        $department->update($validated);

        return redirect()->route('manage.departments.show', $department)
            ->with('success', 'Department updated successfully.');
    }

    public function destroy(Department $department)
    {
        $department->delete();

        return redirect()->route('manage.departments.index')
            ->with('success', 'Department deleted successfully.');
    }
}
