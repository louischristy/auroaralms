<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Middleware\ResolveSubdomain;
use App\Http\Middleware\SetLocale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class ClientSettingsController extends Controller
{
    public function edit()
    {
        $tenant = $this->tenant();

        return view('client.settings.edit', [
            'tenant' => $tenant,
            'locales' => SetLocale::SUPPORTED,
            'baseDomain' => config('app.base_domain') ?: parse_url((string) config('app.url'), PHP_URL_HOST),
        ]);
    }

    public function update(Request $request)
    {
        $tenant = $this->tenant();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'subdomain' => [
                'nullable', 'string', 'min:3', 'max:63', 'alpha_dash',
                Rule::notIn(ResolveSubdomain::RESERVED),
                Rule::unique('tenants', 'subdomain')->ignore($tenant->id),
            ],
            'locale' => ['required', Rule::in(array_keys(SetLocale::SUPPORTED))],
        ]);

        $data['subdomain'] = $data['subdomain'] ? strtolower($data['subdomain']) : null;
        $tenant->update($data);
        Cache::forget("branding_{$tenant->id}");

        return back()->with('success', 'Settings updated.');
    }

    private function tenant()
    {
        $tenant = app()->bound('current_tenant') ? app('current_tenant') : null;
        abort_unless($tenant, 403, 'No tenant context.');

        return $tenant;
    }
}
