@extends('layouts.app')
@section('title', 'Virtual Classroom — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Virtual Classroom</h1>
            <p class="text-sm text-gray-500 mt-1">Schedule live sessions on Zoom, Teams, Meet and more.</p>
        </div>
        <a href="{{ route('manage.virtual-classrooms.create') }}" class="btn-primary">+ Schedule Session</a>
    </div>

    {{-- Filters + view toggle --}}
    <form method="GET" class="card p-4 flex flex-col sm:flex-row gap-3 items-end">
        <input type="hidden" name="view" value="{{ $view }}">
        <div class="flex-1 w-full">
            <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by title..." class="input w-full">
        </div>
        @if($view === 'list')
            <div class="w-full sm:w-40">
                <label class="block text-xs font-medium text-gray-500 mb-1">Show</label>
                <select name="status" class="input w-full">
                    @foreach(['upcoming' => 'Upcoming', 'past' => 'Past', 'all' => 'All'] as $val => $label)
                        <option value="{{ $val }}" {{ $status === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        @endif
        <div class="flex gap-2">
            <button type="submit" class="btn-primary">Filter</button>
            <a href="{{ route('manage.virtual-classrooms.index', ['view' => $view]) }}" class="btn-secondary">Clear</a>
        </div>
        <div class="inline-flex rounded-lg border border-gray-300 overflow-hidden">
            <a href="{{ route('manage.virtual-classrooms.index', array_merge(request()->except('view', 'page'), ['view' => 'list'])) }}"
               class="px-3 py-2 text-sm font-medium {{ $view === 'list' ? 'bg-primary text-white' : 'bg-white text-gray-600 hover:bg-gray-50' }}">List</a>
            <a href="{{ route('manage.virtual-classrooms.index', array_merge(request()->except('view', 'page'), ['view' => 'calendar'])) }}"
               class="px-3 py-2 text-sm font-medium border-l border-gray-300 {{ $view === 'calendar' ? 'bg-primary text-white' : 'bg-white text-gray-600 hover:bg-gray-50' }}">Calendar</a>
        </div>
    </form>

    @if($view === 'calendar')
        {{-- Calendar --}}
        <div class="card">
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                <a href="{{ route('manage.virtual-classrooms.index', array_merge(request()->except('month'), ['view' => 'calendar', 'month' => $prevMonth])) }}" class="p-2 rounded hover:bg-gray-100 text-gray-600" aria-label="Previous month">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <h2 class="font-semibold text-gray-900">{{ $month->format('F Y') }}</h2>
                <a href="{{ route('manage.virtual-classrooms.index', array_merge(request()->except('month'), ['view' => 'calendar', 'month' => $nextMonth])) }}" class="p-2 rounded hover:bg-gray-100 text-gray-600" aria-label="Next month">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
            <div class="overflow-x-auto">
                <div class="min-w-[700px]">
                    <div class="grid grid-cols-7 bg-gray-50 border-b border-gray-200 text-xs font-medium text-gray-500 text-center">
                        @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $d)<div class="py-2">{{ $d }}</div>@endforeach
                    </div>
                    <div class="grid grid-cols-7 divide-x divide-y divide-gray-100">
                        @for($day = $gridStart; $day->lte($gridEnd); $day = $day->addDay())
                            @php $items = $byDay->get($day->toDateString(), collect()); $inMonth = $day->month === $month->month; @endphp
                            <div class="min-h-[100px] p-1.5 {{ $inMonth ? 'bg-white' : 'bg-gray-50' }}">
                                <span class="inline-flex w-6 h-6 items-center justify-center rounded-full text-xs {{ $day->isToday() ? 'bg-primary text-white font-bold' : ($inMonth ? 'text-gray-700' : 'text-gray-400') }}">{{ $day->day }}</span>
                                <div class="mt-1 space-y-1">
                                    @foreach($items as $item)
                                        <a href="{{ route('manage.virtual-classrooms.attendance', $item->id) }}"
                                           class="block truncate rounded px-1.5 py-0.5 text-[11px] {{ $item->status === 'cancelled' ? 'bg-red-50 text-red-700 line-through' : 'bg-primary/10 text-primary hover:bg-primary/20' }}"
                                           title="{{ $item->title }}">
                                            {{ $item->scheduled_at->copy()->timezone($item->timezone)->format('H:i') }} {{ $item->title }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endfor
                    </div>
                </div>
            </div>
        </div>
    @else
        {{-- List --}}
        <div class="card">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-left px-4 py-3 font-medium text-gray-600">Session</th>
                            @if($showTenant)<th class="text-left px-4 py-3 font-medium text-gray-600">Tenant</th>@endif
                            <th class="text-left px-4 py-3 font-medium text-gray-600">Date &amp; Time</th>
                            <th class="text-left px-4 py-3 font-medium text-gray-600">Duration</th>
                            <th class="text-center px-4 py-3 font-medium text-gray-600">Registered</th>
                            <th class="text-center px-4 py-3 font-medium text-gray-600">Status</th>
                            <th class="text-right px-4 py-3 font-medium text-gray-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($sessions as $session)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        @include('partials.platform-icon', ['platform' => $session->platform, 'size' => 'w-10 h-10'])
                                        <div class="min-w-0">
                                            <p class="font-medium text-gray-900">{{ $session->title }}</p>
                                            <p class="text-xs text-gray-500">
                                                {{ $platforms[$session->platform] ?? ucfirst($session->platform) }}
                                                @if($session->host_name) &middot; Host: {{ $session->host_name }} @endif
                                                @if($session->course) &middot; {{ $session->course->title }} @endif
                                                @if($session->is_recurring) &middot; Repeats {{ $session->recurrence_pattern }} @endif
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                @if($showTenant)<td class="px-4 py-3 text-gray-600">{{ $session->tenant?->name ?? '—' }}</td>@endif
                                <td class="px-4 py-3 whitespace-nowrap text-gray-700">
                                    {{ $session->scheduled_at->copy()->timezone($session->timezone)->format('M j, Y') }}
                                    <span class="block text-xs text-gray-500">{{ $session->scheduled_at->copy()->timezone($session->timezone)->format('g:i A') }} ({{ $session->timezone }})</span>
                                </td>
                                <td class="px-4 py-3 text-gray-600">{{ $session->duration_minutes }} min</td>
                                <td class="px-4 py-3 text-center text-gray-700">
                                    {{ $session->attendees_count }}@if($session->max_participants) / {{ $session->max_participants }}@endif
                                    @if($session->scheduled_at->isPast() && $session->attendees_count)
                                        <span class="block text-xs text-gray-400">{{ $session->attended_count }} attended</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">@include('partials.session-status-badge', ['session' => $session])</td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        <a href="{{ route('manage.virtual-classrooms.attendance', $session->id) }}" class="text-secondary hover:text-primary text-xs font-medium">Attendance</a>
                                        <a href="{{ route('manage.virtual-classrooms.edit', $session->id) }}" class="text-secondary hover:text-primary text-xs font-medium">Edit</a>
                                        <form method="POST" action="{{ route('manage.virtual-classrooms.destroy', $session->id) }}"
                                              x-data @submit="if (!confirm('Delete this session?')) $event.preventDefault()">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700 text-xs font-medium">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="{{ $showTenant ? 7 : 6 }}" class="px-4 py-8 text-center text-gray-400">No sessions found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($sessions->hasPages())
                <div class="px-4 py-3 border-t border-gray-100">{{ $sessions->links() }}</div>
            @endif
        </div>
    @endif
</div>
@endsection
