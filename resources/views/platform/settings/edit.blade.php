@extends('layouts.app')
@section('title', 'Platform Settings — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="max-w-4xl space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Platform Settings</h1>
        <p class="text-sm text-gray-500 mt-1">Manage global platform configuration, branding, and email delivery.</p>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-green-700 text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-red-700 text-sm">
            {{ session('error') }}
        </div>
    @endif

    <form method="POST" action="{{ route('platform.settings.update') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- Branding Section --}}
        <div class="card p-6 space-y-5">
            <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                Branding
            </h3>

            {{-- Logo Upload --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="label">Platform Logo</label>
                    <div class="mt-1 flex items-center gap-4">
                        <div class="w-32 h-16 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center bg-gray-50 overflow-hidden">
                            @if(!empty($settings['brand']['logo_path']))
                                <img src="{{ $settings['brand']['logo_path'] }}" alt="Current logo" class="max-h-14 max-w-28 object-contain">
                            @else
                                <span class="text-xs text-gray-400">No logo</span>
                            @endif
                        </div>
                        <div class="flex-1">
                            <input type="file" name="logo" accept="image/png,image/jpeg,image/svg+xml,image/webp" class="input text-sm">
                            <p class="text-xs text-gray-400 mt-1">PNG, JPG, SVG or WebP. Max 2MB.</p>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="label">Favicon</label>
                    <div class="mt-1 flex items-center gap-4">
                        <div class="w-16 h-16 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center bg-gray-50 overflow-hidden">
                            @if(!empty($settings['brand']['favicon_path']))
                                <img src="{{ $settings['brand']['favicon_path'] }}" alt="Current favicon" class="max-h-12 max-w-12 object-contain">
                            @else
                                <span class="text-xs text-gray-400">None</span>
                            @endif
                        </div>
                        <div class="flex-1">
                            <input type="file" name="favicon" accept="image/png,image/x-icon,image/svg+xml" class="input text-sm">
                            <p class="text-xs text-gray-400 mt-1">PNG, ICO or SVG. Max 512KB.</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Brand Text Settings --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="brand_platform_name" class="label">Platform Name</label>
                    <input type="text" id="brand_platform_name" name="settings[brand][platform_name]" value="{{ old('settings.brand.platform_name', $settings['brand']['platform_name'] ?? '') }}" class="input">
                </div>
                <div>
                    <label for="brand_company_name" class="label">Company Name</label>
                    <input type="text" id="brand_company_name" name="settings[brand][company_name]" value="{{ old('settings.brand.company_name', $settings['brand']['company_name'] ?? '') }}" class="input">
                </div>
            </div>

            {{-- Brand Colors --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                @foreach(['primary_color' => '#2B4C7E', 'secondary_color' => '#3A7BD5', 'accent_color' => '#5BC0EB', 'neutral_color' => '#A8A9AD'] as $colorKey => $default)
                <div>
                    <label for="brand_{{ $colorKey }}" class="label">{{ ucwords(str_replace('_', ' ', $colorKey)) }}</label>
                    <div class="flex items-center gap-2">
                        <input type="color" id="brand_{{ $colorKey }}_picker" value="{{ $settings['brand'][$colorKey] ?? $default }}" class="w-10 h-10 rounded cursor-pointer border border-gray-300" onchange="document.getElementById('brand_{{ $colorKey }}').value = this.value">
                        <input type="text" id="brand_{{ $colorKey }}" name="settings[brand][{{ $colorKey }}]" value="{{ old('settings.brand.' . $colorKey, $settings['brand'][$colorKey] ?? $default) }}" class="input" onchange="document.getElementById('brand_{{ $colorKey }}_picker').value = this.value">
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Powered By --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="brand_powered_by_text" class="label">Powered By Text</label>
                    <input type="text" id="brand_powered_by_text" name="settings[brand][powered_by_text]" value="{{ old('settings.brand.powered_by_text', $settings['brand']['powered_by_text'] ?? '') }}" class="input">
                </div>
                <div>
                    <label for="brand_show_powered_by" class="label">Show Powered By</label>
                    <select id="brand_show_powered_by" name="settings[brand][show_powered_by]" class="input">
                        <option value="1" {{ ($settings['brand']['show_powered_by'] ?? '1') == '1' ? 'selected' : '' }}>Yes</option>
                        <option value="0" {{ ($settings['brand']['show_powered_by'] ?? '1') == '0' ? 'selected' : '' }}>No</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- General Settings --}}
        <div class="card p-6 space-y-4">
            <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                General
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach($settings['general'] ?? [] as $key => $value)
                    <div>
                        <label for="general_{{ $key }}" class="label">{{ ucwords(str_replace('_', ' ', $key)) }}</label>
                        <input type="text" id="general_{{ $key }}" name="settings[general][{{ $key }}]" value="{{ old('settings.general.' . $key, $value) }}" class="input">
                    </div>
                @endforeach
            </div>
        </div>

        {{-- SMTP / Email Configuration --}}
        <div class="card p-6 space-y-5">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    Email / SMTP Configuration
                </h3>
                <span class="text-xs text-gray-400">Tenant admins can override these settings</span>
            </div>
            <p class="text-sm text-gray-500">Configure the SMTP server used for sending notification emails (course assignments, reminders, completions). Tenant admins can override these with their own SMTP settings.</p>

            {{-- SMTP Server Settings --}}
            <div class="border border-gray-200 rounded-lg p-4 space-y-4">
                <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">SMTP Server</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="smtp_host" class="label">SMTP Host</label>
                        <input type="text" id="smtp_host" name="settings[smtp][host]" value="{{ old('settings.smtp.host', $settings['smtp']['host'] ?? '') }}" class="input" placeholder="smtp.gmail.com">
                        <p class="text-xs text-gray-400 mt-1">e.g. smtp.gmail.com, smtp.office365.com</p>
                    </div>
                    <div>
                        <label for="smtp_port" class="label">SMTP Port</label>
                        <input type="number" id="smtp_port" name="settings[smtp][port]" value="{{ old('settings.smtp.port', $settings['smtp']['port'] ?? '587') }}" class="input" placeholder="587">
                        <p class="text-xs text-gray-400 mt-1">Common: 587 (TLS), 465 (SSL), 25 (none)</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="smtp_username" class="label">SMTP Username</label>
                        <input type="text" id="smtp_username" name="settings[smtp][username]" value="{{ old('settings.smtp.username', $settings['smtp']['username'] ?? '') }}" class="input" placeholder="your-email@gmail.com" autocomplete="off">
                    </div>
                    <div>
                        <label for="smtp_password" class="label">SMTP Password</label>
                        <input type="password" id="smtp_password" name="settings[smtp][password]" value="{{ old('settings.smtp.password', $settings['smtp']['password'] ?? '') }}" class="input" placeholder="••••••••" autocomplete="new-password">
                        <p class="text-xs text-gray-400 mt-1">For Gmail, use an App Password</p>
                    </div>
                </div>
                <div>
                    <label for="smtp_encryption" class="label">Encryption</label>
                    <select id="smtp_encryption" name="settings[smtp][encryption]" class="input w-auto">
                        <option value="tls" {{ ($settings['smtp']['encryption'] ?? 'tls') === 'tls' ? 'selected' : '' }}>TLS (Recommended)</option>
                        <option value="ssl" {{ ($settings['smtp']['encryption'] ?? '') === 'ssl' ? 'selected' : '' }}>SSL</option>
                        <option value="none" {{ ($settings['smtp']['encryption'] ?? '') === 'none' ? 'selected' : '' }}>None</option>
                    </select>
                </div>
            </div>

            {{-- Sender Settings --}}
            <div class="border border-gray-200 rounded-lg p-4 space-y-4">
                <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Sender Details</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="email_from_name" class="label">From Name</label>
                        <input type="text" id="email_from_name" name="settings[email][from_name]" value="{{ old('settings.email.from_name', $settings['email']['from_name'] ?? 'Auroara LMS') }}" class="input" placeholder="Auroara LMS">
                    </div>
                    <div>
                        <label for="email_from_address" class="label">From Email Address</label>
                        <input type="email" id="email_from_address" name="settings[email][from_address]" value="{{ old('settings.email.from_address', $settings['email']['from_address'] ?? '') }}" class="input" placeholder="noreply@yourcompany.com">
                    </div>
                </div>
            </div>

            {{-- Notification Settings --}}
            <div class="border border-gray-200 rounded-lg p-4 space-y-4">
                <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Notifications</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="email_welcome_email_enabled" class="label">Send Welcome Email on User Creation</label>
                        <select id="email_welcome_email_enabled" name="settings[email][welcome_email_enabled]" class="input w-auto">
                            <option value="1" {{ ($settings['email']['welcome_email_enabled'] ?? '1') == '1' ? 'selected' : '' }}>Yes</option>
                            <option value="0" {{ ($settings['email']['welcome_email_enabled'] ?? '1') == '0' ? 'selected' : '' }}>No</option>
                        </select>
                    </div>
                    <div>
                        <label for="email_course_reminder_days" class="label">Course Due Reminder (days before)</label>
                        <input type="number" id="email_course_reminder_days" name="settings[email][course_reminder_days]" value="{{ old('settings.email.course_reminder_days', $settings['email']['course_reminder_days'] ?? '3') }}" class="input w-auto" min="1" max="30">
                    </div>
                </div>
            </div>
        </div>

        {{-- Security Settings --}}
        <div class="card p-6 space-y-4">
            <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                Security
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach($settings['security'] ?? [] as $key => $value)
                    <div>
                        <label for="security_{{ $key }}" class="label">{{ ucwords(str_replace('_', ' ', $key)) }}</label>
                        <input type="text" id="security_{{ $key }}" name="settings[security][{{ $key }}]" value="{{ old('settings.security.' . $key, $value) }}" class="input">
                    </div>
                @endforeach
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="btn-primary">Save Settings</button>
        </div>
    </form>

    {{-- Test Email Section (separate form) --}}
    <div class="card p-6 space-y-4">
        <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
            <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
            Send Test Email
        </h3>
        <p class="text-sm text-gray-500">Save your SMTP settings above first, then send a test email to verify the configuration works.</p>
        <form method="POST" action="{{ route('platform.settings.test-email') }}" class="flex items-end gap-3">
            @csrf
            <div class="flex-1">
                <label for="test_email_address" class="label">Recipient Email</label>
                <input type="email" id="test_email_address" name="test_email" value="{{ auth()->user()->email }}" class="input" required>
            </div>
            <button type="submit" class="btn-primary whitespace-nowrap">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                Send Test
            </button>
        </form>
    </div>
</div>
@endsection
