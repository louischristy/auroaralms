<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantSsoConfig;
use Illuminate\Http\Request;

class TenantSsoController extends Controller
{
    public function index(Tenant $tenant)
    {
        $configs = TenantSsoConfig::where('tenant_id', $tenant->id)->get();

        return view('platform.tenants.sso', compact('tenant', 'configs'));
    }

    public function store(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'provider'          => 'required|in:google,microsoft',
            'client_id'         => 'required|string|max:255',
            'client_secret'     => 'required|string|max:1024',
            'tenant_identifier' => 'nullable|string|max:255',
            'allowed_domains'   => 'required|string|max:1024',
            'is_active'         => 'boolean',
            'auto_provision'    => 'boolean',
            'force_sso'         => 'boolean',
        ]);

        // Check if provider already exists for this tenant
        $exists = TenantSsoConfig::where('tenant_id', $tenant->id)
            ->where('provider', $validated['provider'])
            ->exists();

        if ($exists) {
            return back()->with('error', 'This provider is already configured for this tenant.');
        }

        $validated['tenant_id'] = $tenant->id;
        $validated['allowed_domains'] = array_map('trim', explode(',', $validated['allowed_domains']));
        $validated['is_active'] = $request->boolean('is_active');
        $validated['auto_provision'] = $request->boolean('auto_provision');
        $validated['force_sso'] = $request->boolean('force_sso');

        TenantSsoConfig::create($validated);

        return back()->with('success', 'SSO provider added successfully.');
    }

    public function update(Request $request, Tenant $tenant, TenantSsoConfig $sso)
    {
        $validated = $request->validate([
            'client_id'         => 'required|string|max:255',
            'client_secret'     => 'nullable|string|max:1024',
            'tenant_identifier' => 'nullable|string|max:255',
            'allowed_domains'   => 'required|string|max:1024',
            'is_active'         => 'boolean',
            'auto_provision'    => 'boolean',
            'force_sso'         => 'boolean',
        ]);

        $validated['allowed_domains'] = array_map('trim', explode(',', $validated['allowed_domains']));
        $validated['is_active'] = $request->boolean('is_active');
        $validated['auto_provision'] = $request->boolean('auto_provision');
        $validated['force_sso'] = $request->boolean('force_sso');

        // Only update secret if provided
        if (empty($validated['client_secret'])) {
            unset($validated['client_secret']);
        }

        $sso->update($validated);

        return back()->with('success', 'SSO configuration updated.');
    }

    public function destroy(Tenant $tenant, TenantSsoConfig $sso)
    {
        $sso->delete();

        return back()->with('success', 'SSO provider removed.');
    }
}
