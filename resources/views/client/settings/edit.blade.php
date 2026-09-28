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
</div>
@endsection
