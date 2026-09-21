@extends('layouts.app')
@section('title', 'Dashboard — ' . ($branding['platform_name'] ?? 'Auroara LMS'))
@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Welcome, {{ $user->name }}</h1>
        <p class="text-sm text-gray-500 mt-1">Continue your cybersecurity awareness training.</p>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="card p-4">
            <p class="text-2xl font-bold text-gray-900">{{ $stats['total_enrolled'] }}</p>
            <p class="text-xs text-gray-500">Courses Assigned</p>
        </div>
        <div class="card p-4">
            <p class="text-2xl font-bold text-green-600">{{ $stats['completed'] }}</p>
            <p class="text-xs text-gray-500">Completed</p>
        </div>
        <div class="card p-4">
            <p class="text-2xl font-bold" style="color: var(--color-primary)">{{ $stats['certificates'] }}</p>
            <p class="text-xs text-gray-500">Certificates</p>
        </div>
        <div class="card p-4">
            <p class="text-2xl font-bold {{ $stats['pending_policies'] > 0 ? 'text-yellow-600' : 'text-green-600' }}">{{ $stats['pending_policies'] }}</p>
            <p class="text-xs text-gray-500">Pending Policies</p>
        </div>
    </div>

    @if($stats['overdue'] > 0)
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 flex items-center gap-3">
            <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
            <p class="text-sm text-red-700">You have <strong>{{ $stats['overdue'] }}</strong> overdue course{{ $stats['overdue'] > 1 ? 's' : '' }}. Please complete them as soon as possible.</p>
        </div>
    @endif

    {{-- In Progress Courses --}}
    @if($inProgressCourses->isNotEmpty())
        <div class="card p-5">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Continue Learning</h2>
            <div class="space-y-3">
                @foreach($inProgressCourses->take(3) as $enrollment)
                    <a href="{{ route('learn.courses.show', $enrollment->course) }}" class="flex items-center gap-4 p-3 rounded-lg hover:bg-gray-50 transition-colors">
                        <div class="relative w-12 h-12 shrink-0">
                            <svg class="w-12 h-12 -rotate-90" viewBox="0 0 36 36">
                                <circle cx="18" cy="18" r="15.9" fill="none" stroke="#E5E7EB" stroke-width="3"/>
                                <circle cx="18" cy="18" r="15.9" fill="none" stroke="var(--color-primary)" stroke-width="3" stroke-dasharray="{{ $enrollment->progress_percent }} {{ 100 - $enrollment->progress_percent }}" stroke-linecap="round"/>
                            </svg>
                            <span class="absolute inset-0 flex items-center justify-center text-xs font-bold text-gray-700">{{ $enrollment->progress_percent }}%</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-900 truncate">{{ $enrollment->course->title }}</p>
                            <p class="text-xs text-gray-400">{{ $enrollment->course->category }}</p>
                        </div>
                        <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Gamification --}}
    @if(isset($gamification))
    <div class="card p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-800">Your Progress</h2>
            <a href="{{ route('learn.leaderboard.index') }}" class="text-sm font-medium hover:underline" style="color: var(--color-primary)">View Leaderboard &rarr;</a>
        </div>
        <div class="grid grid-cols-3 gap-4 mb-4">
            <div class="text-center p-3 rounded-lg bg-amber-50">
                <p class="text-2xl font-bold text-amber-600">{{ number_format($gamification['total_points']) }}</p>
                <p class="text-xs text-gray-500">Points</p>
            </div>
            <div class="text-center p-3 rounded-lg bg-indigo-50">
                <p class="text-2xl font-bold text-indigo-600">#{{ $gamification['rank'] }}</p>
                <p class="text-xs text-gray-500">Rank</p>
            </div>
            <div class="text-center p-3 rounded-lg bg-emerald-50">
                <p class="text-2xl font-bold text-emerald-600">{{ $gamification['badges_count'] }}/{{ $gamification['total_badges'] }}</p>
                <p class="text-xs text-gray-500">Badges</p>
            </div>
        </div>
        @if($gamification['recent_badges']->isNotEmpty())
            <div class="flex items-center gap-3 pt-3 border-t border-gray-100">
                <span class="text-xs text-gray-500 shrink-0">Recent:</span>
                @foreach($gamification['recent_badges'] as $ub)
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-indigo-50 text-xs font-medium text-indigo-700" title="{{ $ub->badge->description }}">
                        {{ $ub->badge->icon }} {{ $ub->badge->name }}
                    </span>
                @endforeach
            </div>
        @endif
    </div>
    @endif

    {{-- Quick Links --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <a href="{{ route('learn.courses.index') }}" class="card p-5 hover:shadow-md transition-shadow group">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center group-hover:bg-primary/20 transition-colors">
                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
                <div>
                    <p class="font-semibold text-gray-900 text-sm">My Courses</p>
                    <p class="text-xs text-gray-500">{{ $stats['in_progress'] }} in progress</p>
                </div>
            </div>
        </a>
        <a href="{{ route('learn.certificates.index') }}" class="card p-5 hover:shadow-md transition-shadow group">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center group-hover:bg-green-200 transition-colors">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                </div>
                <div>
                    <p class="font-semibold text-gray-900 text-sm">Certificates</p>
                    <p class="text-xs text-gray-500">{{ $stats['certificates'] }} earned</p>
                </div>
            </div>
        </a>
        <a href="{{ route('learn.policies.index') }}" class="card p-5 hover:shadow-md transition-shadow group">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-accent/10 flex items-center justify-center group-hover:bg-accent/20 transition-colors">
                    <svg class="w-5 h-5 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                </div>
                <div>
                    <p class="font-semibold text-gray-900 text-sm">Policies</p>
                    <p class="text-xs text-gray-500">{{ $stats['pending_policies'] }} pending</p>
                </div>
            </div>
        </a>
    </div>

    {{-- Recent Activity --}}
    @if($recentLessons->isNotEmpty())
        <div class="card p-5">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Recent Activity</h2>
            <div class="space-y-2">
                @foreach($recentLessons as $completion)
                    <div class="flex items-center gap-3 text-sm">
                        <svg class="w-4 h-4 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span class="text-gray-700">Completed <strong>{{ $completion->lesson->title }}</strong></span>
                        <span class="text-gray-400 text-xs">{{ $completion->completed_at->diffForHumans() }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
