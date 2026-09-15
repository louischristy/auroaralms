@extends('layouts.app')
@section('title', 'Platform Settings — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="max-w-3xl space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Platform Settings</h1>
        <p class="text-sm text-gray-500 mt-1">Manage global platform configuration.</p>
    </div>

    <form method="POST" action="{{ route('platform.settings.update') }}" class="space-y-6">
        @csrf
        @method('PUT')

        @foreach($settings as $group => $items)
            <div class="card p-6 space-y-4">
                <h3 class="text-lg font-medium text-gray-800 capitalize">{{ $group }}</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @forelse($items as $key => $value)
                        <div>
                            <label for="{{ $group }}_{{ $key }}" class="label">{{ ucwords(str_replace('_', ' ', $key)) }}</label>
                            <input type="text" id="{{ $group }}_{{ $key }}" name="settings[{{ $group }}][{{ $key }}]" value="{{ old('settings.' . $group . '.' . $key, $value) }}" class="input">
                        </div>
                    @empty
                        <p class="text-sm text-gray-400">No settings in this group.</p>
                    @endforelse
                </div>
            </div>
        @endforeach

        <div class="flex items-center gap-3">
            <button type="submit" class="btn-primary">Save Settings</button>
        </div>
    </form>
</div>
@endsection
