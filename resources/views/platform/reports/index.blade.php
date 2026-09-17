@extends('layouts.app')
@section('title', 'Reports & Analytics — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <h1 class="text-2xl font-bold text-gray-900">Reports & Analytics</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <a href="{{ route('platform.reports.tenant-completion') }}" class="card p-6 hover:shadow-md transition-shadow group">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <h2 class="text-lg font-semibold text-gray-900 group-hover:text-primary">Tenant Completion</h2>
            </div>
            <p class="text-sm text-gray-500">Completion rates across all tenants with monthly trends and CSV export.</p>
        </a>

        <a href="{{ route('platform.reports.course-performance') }}" class="card p-6 hover:shadow-md transition-shadow group">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-lg bg-green-50 text-green-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
                <h2 class="text-lg font-semibold text-gray-900 group-hover:text-primary">Course Performance</h2>
            </div>
            <p class="text-sm text-gray-500">Pass/fail rates, average scores, time spent, and category breakdown.</p>
        </a>

        <a href="{{ route('platform.reports.quiz-analytics') }}" class="card p-6 hover:shadow-md transition-shadow group">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h2 class="text-lg font-semibold text-gray-900 group-hover:text-primary">Quiz Analytics</h2>
            </div>
            <p class="text-sm text-gray-500">Score distributions, hardest questions, and per-course quiz breakdown.</p>
        </a>
    </div>
</div>
@endsection
