<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::with(['department', 'roles'])
            ->when($request->search, fn ($q, $s) => $q->where(fn ($sub) => $sub->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%")))
            ->when($request->department_id, fn ($q, $d) => $q->where('department_id', $d))
            ->latest()
            ->paginate(20);

        $departments = Department::orderBy('name')->get();

        return view('client.users.index', compact('users', 'departments'));
    }

    public function create()
    {
        $departments = Department::orderBy('name')->get();
        return view('client.users.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'employee_id' => ['nullable', 'string', 'max:100'],
            'role' => ['required', 'string', 'in:client-admin,manager,employee'],
        ]);

        $user = User::create([
            'tenant_id' => Auth::user()->tenant_id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make(Str::random(16)),
            'department_id' => $validated['department_id'],
            'job_title' => $validated['job_title'],
            'employee_id' => $validated['employee_id'] ?? null,
            'is_active' => true,
            'must_change_password' => true,
            'email_verified_at' => now(),
        ]);

        $user->assignRole($validated['role']);

        // Send password reset link so the user can set their own password
        Password::sendResetLink(['email' => $user->email]);

        return redirect()->route('manage.users.index')
            ->with('success', "User '{$user->name}' created. A password setup email has been sent to {$user->email}.");
    }

    public function show(User $user)
    {
        $user->load(['department', 'roles']);
        return view('client.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $departments = Department::orderBy('name')->get();
        return view('client.users.edit', compact('user', 'departments'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'employee_id' => ['nullable', 'string', 'max:100'],
            'is_active' => ['boolean'],
            'role' => ['required', 'string', 'in:client-admin,manager,employee'],
        ]);

        $user->update(collect($validated)->except('role')->toArray());
        $user->syncRoles([$validated['role']]);

        return redirect()->route('manage.users.show', $user)
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('manage.users.index')
            ->with('success', 'User deleted successfully.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,xlsx,xls', 'max:5120'],
        ]);

        // TODO: Implement CSV/Excel import with maatwebsite/excel
        return back()->with('info', 'User import feature coming soon.');
    }

    public function export(Request $request)
    {
        // TODO: Implement export
        return back()->with('info', 'User export feature coming soon.');
    }

    public function teamMembers(Request $request)
    {
        $user = Auth::user();
        $members = User::where('department_id', $user->department_id)
            ->with('roles')
            ->orderBy('name')
            ->paginate(20);

        return view('client.users.team', compact('members'));
    }
}
