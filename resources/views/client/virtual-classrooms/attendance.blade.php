@extends('layouts.app')
@section('title', 'Attendance — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
@php
    $local = $session->scheduled_at->copy()->timezone($session->timezone);
    $attended = $attendees->where('status', 'attended')->count();
    $absent = $attendees->where('status', 'absent')->count();
    $full = $session->max_participants && $attendees->count() >= $session->max_participants;
@endphp
<div class="space-y-6">
    <div>
        <a href="{{ route('manage.virtual-classrooms.index') }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; Back to sessions</a>
        <div class="flex items-center gap-4 mt-2">
            @include('partials.platform-icon', ['platform' => $session->platform, 'size' => 'w-12 h-12'])
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $session->title }}</h1>
                <p class="text-sm text-gray-500">
                    {{ $local->format('D, M j, Y \a\t g:i A') }} ({{ $session->timezone }}) &middot; {{ $session->duration_minutes }} min
                    &middot; @include('partials.session-status-badge', ['session' => $session])
                </p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="card p-4"><p class="text-xs text-gray-500">Registered</p><p class="text-2xl font-bold text-gray-900">{{ $attendees->count() }}@if($session->max_participants)<span class="text-sm font-normal text-gray-400"> / {{ $session->max_participants }}</span>@endif</p></div>
        <div class="card p-4"><p class="text-xs text-gray-500">Attended</p><p class="text-2xl font-bold text-green-600">{{ $attended }}</p></div>
        <div class="card p-4"><p class="text-xs text-gray-500">Absent</p><p class="text-2xl font-bold text-red-500">{{ $absent }}</p></div>
        <div class="card p-4"><p class="text-xs text-gray-500">Not marked</p><p class="text-2xl font-bold text-gray-700">{{ $attendees->count() - $attended - $absent }}</p></div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
        {{-- Attendance checklist --}}
        <div class="lg:col-span-3">
            <form method="POST" action="{{ route('manage.virtual-classrooms.mark-attendance', $session->id) }}" class="card" x-data>
                @csrf
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                    <h2 class="font-semibold text-gray-900">Attendance</h2>
                    @if($attendees->isNotEmpty())
                        <div class="flex gap-3 text-xs font-medium">
                            <button type="button" class="text-green-600 hover:text-green-800" @click="$root.querySelectorAll('input[value=attended]').forEach(r => r.checked = true)">Mark all attended</button>
                            <button type="button" class="text-red-500 hover:text-red-700" @click="$root.querySelectorAll('input[value=absent]').forEach(r => r.checked = true)">Mark all absent</button>
                        </div>
                    @endif
                </div>

                @if($attendees->isEmpty())
                    <p class="px-5 py-10 text-center text-gray-400 text-sm">No one is registered yet. Register users to start tracking attendance.</p>
                @else
                    <ul class="divide-y divide-gray-100">
                        @foreach($attendees as $attendee)
                            <li class="flex items-center justify-between gap-3 px-5 py-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $attendee->user?->name ?? 'Deleted user' }}</p>
                                    <p class="text-xs text-gray-500 truncate">{{ $attendee->user?->email }}@if($attendee->user?->department) &middot; {{ $attendee->user->department->name }}@endif</p>
                                </div>
                                <div class="inline-flex rounded-lg overflow-hidden border border-gray-300 flex-shrink-0">
                                    <label class="cursor-pointer">
                                        <input type="radio" class="sr-only peer" name="attendance[{{ $attendee->user_id }}]" value="attended" {{ $attendee->status === 'attended' ? 'checked' : '' }}>
                                        <span class="block px-3 py-1.5 text-xs font-medium text-gray-600 peer-checked:bg-green-600 peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-green-500">Attended</span>
                                    </label>
                                    <label class="cursor-pointer border-l border-gray-300">
                                        <input type="radio" class="sr-only peer" name="attendance[{{ $attendee->user_id }}]" value="absent" {{ $attendee->status === 'absent' ? 'checked' : '' }}>
                                        <span class="block px-3 py-1.5 text-xs font-medium text-gray-600 peer-checked:bg-red-500 peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-red-500">Absent</span>
                                    </label>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                    <div class="px-5 py-4 border-t border-gray-100 flex justify-end">
                        <button type="submit" class="btn-primary">Save Attendance</button>
                    </div>
                @endif
            </form>
        </div>

        {{-- Register users --}}
        <div class="lg:col-span-2">
            <form method="POST" action="{{ route('manage.virtual-classrooms.register-users', $session->id) }}" class="card"
                  x-data="{ search: '', selected: 0 }">
                @csrf
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="font-semibold text-gray-900">Register Users</h2>
                    @if($full)<p class="text-xs text-red-500 mt-1">This session is full.</p>@endif
                </div>

                @error('user_ids')<p class="px-5 pt-3 text-sm text-red-600">{{ $message }}</p>@enderror

                @if($candidates->isEmpty())
                    <p class="px-5 py-8 text-center text-gray-400 text-sm">Everyone in this organization is already registered.</p>
                @else
                    <div class="p-4 pb-2">
                        <input type="text" x-model="search" class="input" placeholder="Filter by name or email...">
                    </div>
                    <ul class="max-h-80 overflow-y-auto divide-y divide-gray-50 px-2">
                        @foreach($candidates as $user)
                            <li x-show="!search || @js(strtolower($user->name . ' ' . $user->email)).includes(search.toLowerCase())">
                                <label class="flex items-center gap-3 px-3 py-2 rounded hover:bg-gray-50 cursor-pointer">
                                    <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" class="rounded border-gray-300"
                                           @change="selected += $event.target.checked ? 1 : -1">
                                    <span class="min-w-0">
                                        <span class="block text-sm text-gray-900 truncate">{{ $user->name }}</span>
                                        <span class="block text-xs text-gray-500 truncate">{{ $user->email }}</span>
                                    </span>
                                </label>
                            </li>
                        @endforeach
                    </ul>
                    <div class="px-5 py-4 border-t border-gray-100 flex items-center justify-between">
                        <span class="text-xs text-gray-500"><span x-text="selected"></span> selected</span>
                        <button type="submit" class="btn-primary" :disabled="selected === 0">Register Selected</button>
                    </div>
                @endif
            </form>
        </div>
    </div>
</div>
@endsection
