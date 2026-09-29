@extends('layouts.app')
@section('title', __('ui.settings') . ' — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <h1 class="text-2xl font-bold text-gray-900">{{ __('ui.settings') }}</h1>

    @if(session('success'))
        <div class="p-3 rounded bg-green-50 text-green-700 text-sm">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('manage.settings.update') }}" class="card p-6 space-y-5">
        @csrf @method('PUT')

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1" for="name">Organisation name</label>
            <input id="name" name="name" type="text" value="{{ old('name', $tenant->name) }}" required class="w-full border-gray-300 rounded-lg">
            @error('name')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1" for="subdomain">Custom subdomain</label>
            <div class="flex items-center gap-2">
                <input id="subdomain" name="subdomain" type="text" value="{{ old('subdomain', $tenant->subdomain) }}" minlength="3" pattern="[A-Za-z0-9_\-]+" class="flex-1 border-gray-300 rounded-lg" placeholder="clientname">
                <span class="text-sm text-gray-500">.{{ $baseDomain }}</span>
            </div>
            <p class="text-xs text-gray-500 mt-1">Letters, numbers, dashes and underscores; at least 3 characters.</p>
            @error('subdomain')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1" for="locale">Default language</label>
            <select id="locale" name="locale" class="w-full border-gray-300 rounded-lg">
                @foreach($locales as $code => $label)
                    <option value="{{ $code }}" @selected(old('locale', $tenant->locale) === $code)>{{ $label }}</option>
                @endforeach
            </select>
            @error('locale')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <button type="submit" class="btn-primary">{{ __('ui.save') }}</button>
    </form>

    {{-- AI Configuration --}}
    <form method="POST" action="{{ route('manage.settings.update') }}" class="card p-6 space-y-5">
        @csrf @method('PUT')
        <input type="hidden" name="_ai_settings" value="1">

        <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
            AI Tools Configuration
        </h3>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1" for="openai_api_key">OpenAI API Key</label>
            <input id="openai_api_key" name="openai_api_key" type="password" value="" placeholder="{{ !empty($tenant->settings['openai_api_key']) ? '••••••••••••••••••••••••' : 'sk-...' }}" class="w-full border-gray-300 rounded-lg">
            <p class="text-xs text-gray-500 mt-1">Your organisation's OpenAI API key for AI-powered features (Course Generator, Quiz Generator, Phishing Generator). Leave blank to keep the current key{{ !empty($tenant->settings['openai_api_key']) ? '' : ' or use the platform default' }}.</p>
            @error('openai_api_key')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        @if(!empty($tenant->settings['openai_api_key']))
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                API Key Configured
            </span>
            <label class="flex items-center gap-1 text-sm text-gray-500">
                <input type="checkbox" name="remove_api_key" value="1" class="rounded border-gray-300">
                Remove key
            </label>
        </div>
        @endif

        <button type="submit" class="btn-primary">Save AI Settings</button>
    </form>
</div>
@endsection
