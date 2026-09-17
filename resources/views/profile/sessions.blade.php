@extends('layouts.app')
@section('title', 'Active Sessions — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="max-w-2xl space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Active Sessions</h1>
            <p class="text-sm text-gray-500 mt-1">Manage your active sessions across devices.</p>
        </div>
        <a href="{{ route('profile.edit') }}" class="btn-outline btn-sm">&larr; Back to Profile</a>
    </div>

    @if(count($sessions) > 1)
    <div class="card p-4" x-data="{ showLogoutAll: false }">
        <div class="flex items-center justify-between">
            <p class="text-sm text-gray-600">You have {{ count($sessions) }} active session(s).</p>
            <button type="button" x-on:click="showLogoutAll = !showLogoutAll" class="btn-danger btn-sm">
                Log Out All Other Sessions
            </button>
        </div>

        <form method="POST" action="{{ route('sessions.destroy-all') }}" x-show="showLogoutAll" x-cloak class="mt-4 pt-4 border-t border-gray-100">
            @csrf
            @method('DELETE')
            <p class="text-sm text-gray-600 mb-3">Enter your password to confirm.</p>
            <div class="flex items-center gap-3">
                <input type="password" name="current_password" placeholder="Current password" required class="input w-48 text-sm">
                <button type="submit" class="btn-danger btn-sm">Confirm</button>
            </div>
            @error('current_password')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </form>
    </div>
    @endif

    <div class="space-y-3">
        @foreach($sessions as $session)
        <div class="card p-4 {{ $session->is_current ? 'ring-2 ring-blue-300' : '' }}">
            <div class="flex items-start justify-between">
                <div class="flex items-start gap-3">
                    <div class="mt-1 text-gray-400">
                        @if($session->device_type === 'mobile')
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="7" y="2" width="10" height="20" rx="2" stroke-width="2"/><circle cx="12" cy="18" r="1"/></svg>
                        @elseif($session->device_type === 'tablet')
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="4" y="2" width="16" height="20" rx="2" stroke-width="2"/><circle cx="12" cy="18" r="1"/></svg>
                        @else
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="2" y="3" width="20" height="14" rx="2" stroke-width="2"/><path d="M8 21h8M12 17v4" stroke-width="2"/></svg>
                        @endif
                    </div>
                    <div>
                        <p class="font-medium text-gray-900">
                            {{ $session->browser }} on {{ $session->platform }}
                            @if($session->is_current)
                                <span class="ml-2 text-xs font-semibold text-green-700 bg-green-100 px-2 py-0.5 rounded-full">This device</span>
                            @endif
                        </p>
                        <p class="text-sm text-gray-500">{{ $session->ip_address }}</p>
                        <p class="text-xs text-gray-400 mt-1">
                            Last active {{ $session->last_active->diffForHumans() }}
                        </p>
                    </div>
                </div>

                @if(!$session->is_current)
                <div x-data="{ confirm: false }">
                    <button type="button" x-on:click="confirm = !confirm" class="text-sm text-red-600 hover:text-red-800">
                        Revoke
                    </button>
                    <form method="POST" action="{{ route('sessions.destroy', $session->id) }}" x-show="confirm" x-cloak class="mt-2">
                        @csrf
                        @method('DELETE')
                        <input type="password" name="current_password" placeholder="Password" required class="input w-32 text-xs">
                        <button type="submit" class="btn-danger btn-sm mt-1 text-xs">Confirm</button>
                    </form>
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
