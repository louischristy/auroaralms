@extends('layouts.app')
@section('title', 'SSO Settings — ' . $tenant->name)

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">SSO Settings</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $tenant->name }} — Single Sign-On configuration</p>
        </div>
        <a href="{{ route('platform.tenants.show', $tenant) }}" class="btn-outline">Back to Tenant</a>
    </div>

    @if(session('success'))
        <div class="p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">{{ session('error') }}</div>
    @endif

    {{-- Existing configs --}}
    @forelse($configs as $config)
        <div class="card p-6" x-data="{ editing: false }">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    @if($config->provider === 'google')
                        <svg class="w-6 h-6" viewBox="0 0 24 24"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
                    @else
                        <svg class="w-6 h-6" viewBox="0 0 21 21"><rect x="1" y="1" width="9" height="9" fill="#F25022"/><rect x="11" y="1" width="9" height="9" fill="#7FBA00"/><rect x="1" y="11" width="9" height="9" fill="#00A4EF"/><rect x="11" y="11" width="9" height="9" fill="#FFB900"/></svg>
                    @endif
                    <div>
                        <h3 class="font-semibold text-gray-900">{{ \App\Models\TenantSsoConfig::providers()[$config->provider] ?? ucfirst($config->provider) }}</h3>
                        <p class="text-xs text-gray-500">Domains: {{ implode(', ', $config->allowed_domains ?? []) }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    @if($config->is_active)
                        <span class="badge-success">Active</span>
                    @else
                        <span class="badge-danger">Inactive</span>
                    @endif
                    @if($config->force_sso)
                        <span class="px-2 py-0.5 text-xs font-medium bg-amber-100 text-amber-800 rounded-full">Forced</span>
                    @endif
                    @if($config->auto_provision)
                        <span class="px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">Auto-provision</span>
                    @endif
                    <button @click="editing = !editing" class="btn-outline text-sm" x-text="editing ? 'Cancel' : 'Edit'"></button>
                </div>
            </div>

            <div x-show="editing" x-cloak>
                <form method="POST" action="{{ route('platform.tenants.sso.update', [$tenant, $config]) }}" class="space-y-4 border-t pt-4">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="label">Client ID</label>
                            <input type="text" name="client_id" value="{{ $config->client_id }}" required class="input">
                        </div>
                        <div>
                            <label class="label">Client Secret</label>
                            <input type="password" name="client_secret" placeholder="Leave blank to keep current" class="input">
                        </div>
                        @if($config->provider === 'microsoft')
                        <div>
                            <label class="label">Azure Tenant ID</label>
                            <input type="text" name="tenant_identifier" value="{{ $config->tenant_identifier }}" class="input" placeholder="e.g. common, organizations, or UUID">
                        </div>
                        @endif
                        <div>
                            <label class="label">Allowed Domains (comma-separated)</label>
                            <input type="text" name="allowed_domains" value="{{ implode(', ', $config->allowed_domains ?? []) }}" required class="input" placeholder="example.com, corp.example.com">
                        </div>
                    </div>

                    <div class="flex items-center gap-6">
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" name="is_active" value="1" {{ $config->is_active ? 'checked' : '' }} class="rounded border-gray-300 text-primary focus:ring-primary">
                            Active
                        </label>
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" name="auto_provision" value="1" {{ $config->auto_provision ? 'checked' : '' }} class="rounded border-gray-300 text-primary focus:ring-primary">
                            Auto-provision users
                        </label>
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" name="force_sso" value="1" {{ $config->force_sso ? 'checked' : '' }} class="rounded border-gray-300 text-primary focus:ring-primary">
                            Force SSO (disable password)
                        </label>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="submit" class="btn-primary">Update</button>
                    </div>
                </form>
                <form method="POST" action="{{ route('platform.tenants.sso.destroy', [$tenant, $config]) }}" onsubmit="return confirm('Remove this SSO provider?')" class="mt-2">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-outline text-red-600 border-red-300 hover:bg-red-50 text-sm">Remove Provider</button>
                </form>
            </div>
        </div>
    @empty
        <div class="card p-6 text-center text-gray-500 text-sm">No SSO providers configured for this tenant.</div>
    @endforelse

    {{-- Add new provider --}}
    @php
        $existingProviders = $configs->pluck('provider')->toArray();
        $availableProviders = array_diff_key(\App\Models\TenantSsoConfig::providers(), array_flip($existingProviders));
    @endphp

    @if(count($availableProviders))
    <div class="card p-6" x-data="{ open: false }">
        <button @click="open = !open" class="flex items-center gap-2 text-sm font-medium text-primary hover:text-primary/80">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            Add SSO Provider
        </button>

        <div x-show="open" x-cloak class="mt-4 border-t pt-4">
            <form method="POST" action="{{ route('platform.tenants.sso.store', $tenant) }}" class="space-y-4" x-data="{ provider: '' }">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label">Provider</label>
                        <select name="provider" required class="input" x-model="provider">
                            <option value="">Select…</option>
                            @foreach($availableProviders as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label">Allowed Domains (comma-separated)</label>
                        <input type="text" name="allowed_domains" required class="input" placeholder="example.com, corp.example.com">
                    </div>
                    <div>
                        <label class="label">Client ID</label>
                        <input type="text" name="client_id" required class="input">
                    </div>
                    <div>
                        <label class="label">Client Secret</label>
                        <input type="password" name="client_secret" required class="input">
                    </div>
                    <div x-show="provider === 'microsoft'">
                        <label class="label">Azure Tenant ID</label>
                        <input type="text" name="tenant_identifier" class="input" placeholder="e.g. common, organizations, or UUID">
                    </div>
                </div>

                <div class="flex items-center gap-6">
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300 text-primary focus:ring-primary">
                        Active
                    </label>
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="auto_provision" value="1" class="rounded border-gray-300 text-primary focus:ring-primary">
                        Auto-provision users
                    </label>
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="force_sso" value="1" class="rounded border-gray-300 text-primary focus:ring-primary">
                        Force SSO (disable password)
                    </label>
                </div>

                <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg text-xs text-blue-800 space-y-1">
                    <p><strong>Google Workspace:</strong> Create OAuth credentials at <em>console.cloud.google.com</em> → APIs & Services → Credentials. Set authorized redirect URI to: <code class="bg-blue-100 px-1 rounded">{{ route('sso.callback', ['provider' => 'google']) }}</code></p>
                    <p><strong>Microsoft 365:</strong> Register an app at <em>entra.microsoft.com</em> → App registrations. Set redirect URI to: <code class="bg-blue-100 px-1 rounded">{{ route('sso.callback', ['provider' => 'microsoft']) }}</code></p>
                </div>

                <button type="submit" class="btn-primary">Add Provider</button>
            </form>
        </div>
    </div>
    @endif
</div>
@endsection
