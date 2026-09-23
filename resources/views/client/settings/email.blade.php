@extends('layouts.app')
@section('title', 'Email Settings — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="max-w-3xl space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Email Settings</h1>
        <p class="text-sm text-gray-500 mt-1">Configure your organization's email delivery. Leave fields empty to use the platform default settings.</p>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-green-700 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-red-700 text-sm">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('manage.settings.email.update') }}" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- SMTP Override --}}
        <div class="card p-6 space-y-5">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    SMTP Configuration
                </h3>
                @if($usingPlatformDefaults)
                    <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded-full bg-blue-50 text-blue-700">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                        Using platform defaults
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded-full bg-green-50 text-green-700">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        Custom SMTP configured
                    </span>
                @endif
            </div>
            <p class="text-sm text-gray-500">Override the platform SMTP settings with your organization's own mail server. Leave all fields empty to use platform defaults.</p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="smtp_host" class="label">SMTP Host</label>
                    <input type="text" id="smtp_host" name="smtp_host" value="{{ old('smtp_host', $tenantSmtp['host'] ?? '') }}" class="input" placeholder="{{ $platformDefaults['host'] ?? 'Platform default' }}">
                </div>
                <div>
                    <label for="smtp_port" class="label">SMTP Port</label>
                    <input type="number" id="smtp_port" name="smtp_port" value="{{ old('smtp_port', $tenantSmtp['port'] ?? '') }}" class="input" placeholder="{{ $platformDefaults['port'] ?? '587' }}">
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="smtp_username" class="label">SMTP Username</label>
                    <input type="text" id="smtp_username" name="smtp_username" value="{{ old('smtp_username', $tenantSmtp['username'] ?? '') }}" class="input" placeholder="Leave empty for platform default" autocomplete="off">
                </div>
                <div>
                    <label for="smtp_password" class="label">SMTP Password</label>
                    <input type="password" id="smtp_password" name="smtp_password" value="{{ old('smtp_password', $tenantSmtp['password'] ?? '') }}" class="input" placeholder="••••••••" autocomplete="new-password">
                </div>
            </div>
            <div>
                <label for="smtp_encryption" class="label">Encryption</label>
                <select id="smtp_encryption" name="smtp_encryption" class="input w-auto">
                    <option value="" {{ empty($tenantSmtp['encryption'] ?? '') ? 'selected' : '' }}>Platform default</option>
                    <option value="tls" {{ ($tenantSmtp['encryption'] ?? '') === 'tls' ? 'selected' : '' }}>TLS</option>
                    <option value="ssl" {{ ($tenantSmtp['encryption'] ?? '') === 'ssl' ? 'selected' : '' }}>SSL</option>
                    <option value="none" {{ ($tenantSmtp['encryption'] ?? '') === 'none' ? 'selected' : '' }}>None</option>
                </select>
            </div>
        </div>

        {{-- Sender Override --}}
        <div class="card p-6 space-y-4">
            <h3 class="text-lg font-semibold text-gray-800">Sender Details</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="from_name" class="label">From Name</label>
                    <input type="text" id="from_name" name="from_name" value="{{ old('from_name', $tenantSmtp['from_name'] ?? '') }}" class="input" placeholder="{{ $platformDefaults['from_name'] ?? 'Platform default' }}">
                </div>
                <div>
                    <label for="from_address" class="label">From Email Address</label>
                    <input type="email" id="from_address" name="from_address" value="{{ old('from_address', $tenantSmtp['from_address'] ?? '') }}" class="input" placeholder="{{ $platformDefaults['from_address'] ?? 'Platform default' }}">
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="btn-primary">Save Email Settings</button>
            @if(!$usingPlatformDefaults)
                <button type="button" onclick="if(confirm('Reset to platform defaults? This will remove your custom SMTP settings.')) document.getElementById('reset-form').submit();" class="btn-secondary text-red-600 hover:text-red-700">
                    Reset to Platform Defaults
                </button>
            @endif
        </div>
    </form>

    @if(!$usingPlatformDefaults)
    <form id="reset-form" method="POST" action="{{ route('manage.settings.email.reset') }}" class="hidden">
        @csrf
        @method('DELETE')
    </form>
    @endif

    {{-- Test Email --}}
    <div class="card p-6 space-y-4">
        <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
            <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
            Send Test Email
        </h3>
        <form method="POST" action="{{ route('manage.settings.email.test') }}" class="flex items-end gap-3">
            @csrf
            <div class="flex-1">
                <label for="test_email" class="label">Recipient Email</label>
                <input type="email" id="test_email" name="test_email" value="{{ auth()->user()->email }}" class="input" required>
            </div>
            <button type="submit" class="btn-primary whitespace-nowrap">Send Test</button>
        </form>
    </div>
</div>
@endsection
