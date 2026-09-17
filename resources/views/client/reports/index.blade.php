@extends('layouts.app')
@section('title', 'Reports — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <h1 class="text-2xl font-bold text-gray-900">Reports</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <a href="{{ route('manage.reports.user-progress') }}" class="card p-6 hover:shadow-md transition-shadow group">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <h2 class="text-lg font-semibold text-gray-900 group-hover:text-primary">User Progress</h2>
            </div>
            <p class="text-sm text-gray-500">Per-employee completion breakdown with department filtering and CSV export.</p>
        </a>

        <a href="{{ route('manage.reports.department-breakdown') }}" class="card p-6 hover:shadow-md transition-shadow group">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-lg bg-green-50 text-green-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2z"/></svg>
                </div>
                <h2 class="text-lg font-semibold text-gray-900 group-hover:text-primary">Department Breakdown</h2>
            </div>
            <p class="text-sm text-gray-500">Completion rates by department with visual comparison.</p>
        </a>

        <a href="{{ route('manage.reports.overdue-training') }}" class="card p-6 hover:shadow-md transition-shadow group">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-lg bg-red-50 text-red-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h2 class="text-lg font-semibold text-gray-900 group-hover:text-primary">Overdue Training</h2>
            </div>
            <p class="text-sm text-gray-500">Users with past-due courses sorted by urgency.</p>
        </a>
    </div>
</div>
@endsection
