@extends('layouts.app')
@section('title', 'Platform Settings — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="max-w-3xl space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Platform Settings</h1>
        <p class="text-sm text-gray-500 mt-1">Manage global platform configuration and branding.</p>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-green-700 text-sm">
            {{ session('success') }}
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
                <div>
                    <label for="brand_primary_color" class="label">Primary Color</label>
                    <div class="flex items-center gap-2">
                        <input type="color" id="brand_primary_color_picker" value="{{ $settings['brand']['primary_color'] ?? '#2B4C7E' }}" class="w-10 h-10 rounded cursor-pointer border border-gray-300" onchange="document.getElementById('brand_primary_color').value = this.value">
                        <input type="text" id="brand_primary_color" name="settings[brand][primary_color]" value="{{ old('settings.brand.primary_color', $settings['brand']['primary_color'] ?? '#2B4C7E') }}" class="input" onchange="document.getElementById('brand_primary_color_picker').value = this.value">
                    </div>
                </div>
                <div>
                    <label for="brand_secondary_color" class="label">Secondary Color</label>
                    <div class="flex items-center gap-2">
                        <input type="color" id="brand_secondary_color_picker" value="{{ $settings['brand']['secondary_color'] ?? '#3A7BD5' }}" class="w-10 h-10 rounded cursor-pointer border border-gray-300" onchange="document.getElementById('brand_secondary_color').value = this.value">
                        <input type="text" id="brand_secondary_color" name="settings[brand][secondary_color]" value="{{ old('settings.brand.secondary_color', $settings['brand']['secondary_color'] ?? '#3A7BD5') }}" class="input" onchange="document.getElementById('brand_secondary_color_picker').value = this.value">
                    </div>
                </div>
                <div>
                    <label for="brand_accent_color" class="label">Accent Color</label>
                    <div class="flex items-center gap-2">
                        <input type="color" id="brand_accent_color_picker" value="{{ $settings['brand']['accent_color'] ?? '#5BC0EB' }}" class="w-10 h-10 rounded cursor-pointer border border-gray-300" onchange="document.getElementById('brand_accent_color').value = this.value">
                        <input type="text" id="brand_accent_color" name="settings[brand][accent_color]" value="{{ old('settings.brand.accent_color', $settings['brand']['accent_color'] ?? '#5BC0EB') }}" class="input" onchange="document.getElementById('brand_accent_color_picker').value = this.value">
                    </div>
                </div>
                <div>
                    <label for="brand_neutral_color" class="label">Neutral Color</label>
                    <div class="flex items-center gap-2">
                        <input type="color" id="brand_neutral_color_picker" value="{{ $settings['brand']['neutral_color'] ?? '#A8A9AD' }}" class="w-10 h-10 rounded cursor-pointer border border-gray-300" onchange="document.getElementById('brand_neutral_color').value = this.value">
                        <input type="text" id="brand_neutral_color" name="settings[brand][neutral_color]" value="{{ old('settings.brand.neutral_color', $settings['brand']['neutral_color'] ?? '#A8A9AD') }}" class="input" onchange="document.getElementById('brand_neutral_color_picker').value = this.value">
                    </div>
                </div>
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

        {{-- Email Settings --}}
        <div class="card p-6 space-y-4">
            <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                Email
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach($settings['email'] ?? [] as $key => $value)
                    <div>
                        <label for="email_{{ $key }}" class="label">{{ ucwords(str_replace('_', ' ', $key)) }}</label>
                        <input type="text" id="email_{{ $key }}" name="settings[email][{{ $key }}]" value="{{ old('settings.email.' . $key, $value) }}" class="input">
                    </div>
                @endforeach
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

        {{-- AI Configuration --}}
        <div class="card p-6 space-y-4">
            <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                AI Tools (Platform Default)
            </h3>
            <p class="text-sm text-gray-500">This API key is used as the default for tenants that haven't configured their own key.</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="ai_openai_api_key" class="label">OpenAI API Key</label>
                    <input type="password" id="ai_openai_api_key" name="settings[ai][openai_api_key]" value="" placeholder="{{ !empty($settings['ai']['openai_api_key']) ? '••••••••••••••••••••••••' : 'sk-...' }}" class="input">
                    <p class="text-xs text-gray-400 mt-1">Leave blank to keep current key.</p>
                </div>
                <div>
                    <label for="ai_openai_model" class="label">AI Model</label>
                    <input type="text" id="ai_openai_model" name="settings[ai][openai_model]" value="{{ old('settings.ai.openai_model', $settings['ai']['openai_model'] ?? 'gpt-4o-mini') }}" class="input">
                    <p class="text-xs text-gray-400 mt-1">e.g. gpt-4o-mini, gpt-4o, gpt-4-turbo</p>
                </div>
            </div>
            @if(!empty($settings['ai']['openai_api_key']))
            <div class="flex items-center gap-3 p-3 rounded-lg bg-green-50 border border-green-200">
                <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                <div>
                    <span class="text-sm font-medium text-green-800">OpenAI API Connected</span>
                    <p class="text-xs text-green-600">Platform default API key is configured and active.</p>
                </div>
            </div>
            <label class="flex items-center gap-2 mt-2">
                <input type="checkbox" name="remove_openai_key" value="1" class="rounded border-gray-300 text-indigo-600">
                <span class="text-sm text-gray-600">Remove API key</span>
            </label>
            @else
            <div class="flex items-center gap-3 p-3 rounded-lg bg-gray-50 border border-gray-200">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                <div>
                    <span class="text-sm font-medium text-gray-600">Not Connected</span>
                    <p class="text-xs text-gray-500">Enter an OpenAI API key to enable AI-powered features.</p>
                </div>
            </div>
            @endif
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="btn-primary">Save Settings</button>
        </div>
    </form>
</div>
@endsection
