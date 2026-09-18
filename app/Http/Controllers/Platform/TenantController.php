<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class TenantController extends Controller
{
    public function index()
    {
        $tenants = Tenant::withCount('users')->latest()->paginate(15);
        return view('platform.tenants.index', compact('tenants'));
    }

    public function create()
    {
        return view('platform.tenants.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:100', 'unique:tenants,slug'],
            'domain' => ['nullable', 'string', 'max:255', 'unique:tenants,domain'],
            'primary_color' => ['nullable', 'string', 'max:7'],
            'accent_color' => ['nullable', 'string', 'max:7'],
            'max_users' => ['required', 'integer', 'min:1'],
            'subscription_plan' => ['required', 'string', 'in:starter,standard,professional,enterprise'],
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'email', 'max:255'],
        ]);

        $validated['slug'] = $validated['slug'] ?: Str::slug($validated['name']);

        $tenant = Tenant::create(collect($validated)->except(['admin_name', 'admin_email'])->toArray());

        // Create client admin for the tenant
        $admin = $tenant->users()->create([
            'name' => $validated['admin_name'],
            'email' => $validated['admin_email'],
            'password' => bcrypt(Str::random(16)),
            'is_active' => true,
            'must_change_password' => true,
            'email_verified_at' => now(),
        ]);
        $admin->assignRole('client-admin');

        // Send password reset link so the admin can set their own password
        Password::sendResetLink(['email' => $admin->email]);

        return redirect()->route('platform.tenants.index')
            ->with('success', "Tenant '{$tenant->name}' created. A password setup email has been sent to {$admin->email}.");
    }

    public function show(Tenant $tenant)
    {
        $tenant->loadCount('users');
        $tenant->load('users');
        return view('platform.tenants.show', compact('tenant'));
    }

    public function edit(Tenant $tenant)
    {
        return view('platform.tenants.edit', compact('tenant'));
    }

    public function update(Request $request, Tenant $tenant)
    {
        // Handle security-only update (2FA toggle from show page)
        if ($request->input('_security_only')) {
            $tenant->update([
                'require_two_factor' => $request->boolean('require_two_factor'),
            ]);

            return redirect()->route('platform.tenants.show', $tenant)
                ->with('success', 'Security settings updated.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:100', 'unique:tenants,slug,' . $tenant->id],
            'domain' => ['nullable', 'string', 'max:255', 'unique:tenants,domain,' . $tenant->id],
            'primary_color' => ['nullable', 'string', 'max:7'],
            'accent_color' => ['nullable', 'string', 'max:7'],
            'max_users' => ['required', 'integer', 'min:1'],
            'subscription_plan' => ['required', 'string'],
        ]);

        $tenant->update($validated);

        return redirect()->route('platform.tenants.show', $tenant)
            ->with('success', 'Tenant updated successfully.');
    }

    public function destroy(Tenant $tenant)
    {
        $tenant->delete();

        return redirect()->route('platform.tenants.index')
            ->with('success', 'Tenant deleted successfully.');
    }

    public function toggleStatus(Tenant $tenant)
    {
        $tenant->update(['is_active' => ! $tenant->is_active]);

        $status = $tenant->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Tenant {$status} successfully.");
    }
}
