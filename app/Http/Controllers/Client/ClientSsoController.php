<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\TenantSsoConfig;
use Illuminate\Http\Request;

class ClientSsoController extends Controller
{
    private function tenant()
    {
        $tenant = app()->bound('current_tenant') ? app('current_tenant') : null;
        abort_unless($tenant, 403, 'No tenant context.');
        return $tenant;
    }

    public function index()
    {
        $tenant = $this->tenant();
        $configs = TenantSsoConfig::where('tenant_id', $tenant->id)->get();

        return view('client.sso.index', compact('tenant', 'configs'));
    }

    public function store(Request $request)
    {
        $tenant = $this->tenant();

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

        $exists = TenantSsoConfig::where('tenant_id', $tenant->id)
            ->where('provider', $validated['provider'])
            ->exists();

        if ($exists) {
            return back()->with('error', 'This provider is already configured.');
        }

        $validated['tenant_id'] = $tenant->id;
        $validated['allowed_domains'] = array_map('trim', explode(',', $validated['allowed_domains']));
        $validated['is_active'] = $request->boolean('is_active');
        $validated['auto_provision'] = $request->boolean('auto_provision');
        $validated['force_sso'] = $request->boolean('force_sso');

        TenantSsoConfig::create($validated);

        return back()->with('success', 'SSO provider added successfully.');
    }

    public function update(Request $request, TenantSsoConfig $sso)
    {
        $tenant = $this->tenant();
        abort_unless($sso->tenant_id === $tenant->id, 403);

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

        if (empty($validated['client_secret'])) {
            unset($validated['client_secret']);
        }

        $sso->update($validated);

        return back()->with('success', 'SSO configuration updated.');
    }

    public function destroy(TenantSsoConfig $sso)
    {
        $tenant = $this->tenant();
        abort_unless($sso->tenant_id === $tenant->id, 403);

        $sso->delete();

        return back()->with('success', 'SSO provider removed.');
    }
}
