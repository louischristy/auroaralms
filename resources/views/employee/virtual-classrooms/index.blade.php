@extends('layouts.app')
@section('title', 'Virtual Classroom — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
@php
    $platformNames = ['zoom' => 'Zoom', 'teams' => 'Microsoft Teams', 'meet' => 'Google Meet', 'webex' => 'Webex', 'other' => 'Online'];
@endphp
<div class="space-y-8">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Virtual Classroom</h1>
        <p class="text-sm text-gray-500 mt-1">Join live sessions and revisit recordings.</p>
    </div>

    {{-- My upcoming sessions --}}
    <section class="space-y-3">
        <h2 class="text-sm font-semibold uppercase tracking-wider text-gray-400">My Upcoming Sessions</h2>
        @if($registered->isEmpty())
            <div class="card p-8 text-center text-gray-400 text-sm">You haven't registered for any upcoming sessions.</div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($registered as $s)
                    @php $local = $s->scheduled_at->copy()->timezone($s->timezone); $inProgress = $s->scheduled_at->isPast(); @endphp
                    <div class="card p-5 {{ $inProgress ? 'ring-2 ring-green-400' : '' }}">
                        <div class="flex items-start gap-4">
                            @include('partials.platform-icon', ['platform' => $s->platform, 'size' => 'w-12 h-12'])
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 mb-1">@include('partials.session-status-badge', ['session' => $s])</div>
                                <h3 class="font-semibold text-gray-900">{{ $s->title }}</h3>
                                <p class="text-sm text-gray-600 mt-1">{{ $local->format('D, M j, Y') }} &middot; {{ $local->format('g:i A') }} ({{ $s->timezone }})</p>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    {{ $platformNames[$s->platform] ?? 'Online' }} &middot; {{ $s->duration_minutes }} min
                                    @if($s->host_name) &middot; Host: {{ $s->host_name }} @endif
                                </p>
                                @if($s->course)<p class="text-xs text-gray-500 mt-0.5">Course: {{ $s->course->title }}</p>@endif
                                @if($s->meeting_id || $s->passcode)
                                    <p class="text-xs text-gray-500 mt-2">
                                        @if($s->meeting_id)Meeting ID: <span class="font-mono">{{ $s->meeting_id }}</span>@endif
                                        @if($s->passcode) &middot; Passcode: <span class="font-mono">{{ $s->passcode }}</span>@endif
                                    </p>
                                @endif
                            </div>
                        </div>
                        <a href="{{ $s->meeting_url }}" target="_blank" rel="noopener noreferrer"
                           class="mt-4 btn-primary w-full">{{ $inProgress ? 'Join Now' : 'Join Session' }}</a>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    {{-- Available sessions --}}
    <section class="space-y-3">
        <h2 class="text-sm font-semibold uppercase tracking-wider text-gray-400">Available Sessions</h2>
        @if($available->isEmpty())
            <div class="card p-8 text-center text-gray-400 text-sm">No other sessions are open for registration.</div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($available as $s)
                    @php
                        $local = $s->scheduled_at->copy()->timezone($s->timezone);
                        $spotsLeft = $s->max_participants ? max($s->max_participants - $s->attendees_count, 0) : null;
                    @endphp
                    <div class="card p-5 flex flex-col">
                        <div class="flex items-start gap-4">
                            @include('partials.platform-icon', ['platform' => $s->platform, 'size' => 'w-12 h-12'])
                            <div class="min-w-0 flex-1">
                                <h3 class="font-semibold text-gray-900">{{ $s->title }}</h3>
                                <p class="text-sm text-gray-600 mt-1">{{ $local->format('D, M j, Y') }} &middot; {{ $local->format('g:i A') }} ({{ $s->timezone }})</p>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    {{ $platformNames[$s->platform] ?? 'Online' }} &middot; {{ $s->duration_minutes }} min
                                    @if($s->host_name) &middot; Host: {{ $s->host_name }} @endif
                                </p>
                                @if($s->description)<p class="text-sm text-gray-500 mt-2">{{ \Illuminate\Support\Str::limit($s->description, 110) }}</p>@endif
                            </div>
                        </div>
                        <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between">
                            <span class="text-xs {{ $spotsLeft === 0 ? 'text-red-500' : 'text-gray-500' }}">
                                @if($spotsLeft === null) Open to all
                                @elseif($spotsLeft === 0) Session full
                                @else {{ $spotsLeft }} {{ \Illuminate\Support\Str::plural('spot', $spotsLeft) }} left @endif
                            </span>
                            <form method="POST" action="{{ route('learn.virtual-classrooms.register', $s->id) }}">
                                @csrf
                                <button type="submit" class="btn-secondary btn-sm" {{ $spotsLeft === 0 ? 'disabled' : '' }}>Register</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    {{-- Past sessions --}}
    @if($past->isNotEmpty())
        <section class="space-y-3">
            <h2 class="text-sm font-semibold uppercase tracking-wider text-gray-400">Past Sessions</h2>
            <div class="card divide-y divide-gray-100">
                @foreach($past as $s)
                    @php $local = $s->scheduled_at->copy()->timezone($s->timezone); $att = $attendance[$s->id] ?? 'registered'; @endphp
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4">
                        <div class="flex items-center gap-3 min-w-0">
                            @include('partials.platform-icon', ['platform' => $s->platform, 'size' => 'w-10 h-10'])
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ $s->title }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ $local->format('M j, Y \a\t g:i A') }}
                                    @if($s->host_name) &middot; Host: {{ $s->host_name }} @endif
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 flex-shrink-0">
                            @if($att === 'attended')<span class="badge-success">Attended</span>
                            @elseif($att === 'absent')<span class="badge-danger">Missed</span>@endif
                            @if($s->recording_url)
                                <a href="{{ $s->recording_url }}" target="_blank" rel="noopener noreferrer" class="btn-outline btn-sm">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Watch Recording
                                </a>
                            @else
                                <span class="text-xs text-gray-400">No recording</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
</div>
@endsection
