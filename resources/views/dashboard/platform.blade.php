@extends('layouts.app')
@section('title', 'Platform Dashboard — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Platform Dashboard</h1>
        <p class="text-sm text-gray-500 mt-1">Cross-tenant analytics and platform overview.</p>
    </div>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="card p-4">
            <p class="text-2xl font-bold text-gray-900">{{ $stats['active_tenants'] }}</p>
            <p class="text-xs text-gray-500">Active Tenants</p>
        </div>
        <div class="card p-4">
            <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_users']) }}</p>
            <p class="text-xs text-gray-500">Total Users</p>
        </div>
        <div class="card p-4">
            <p class="text-2xl font-bold" style="color: var(--color-primary)">{{ $stats['completion_rate'] }}%</p>
            <p class="text-xs text-gray-500">Completion Rate</p>
        </div>
        <div class="card p-4">
            <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_certificates']) }}</p>
            <p class="text-xs text-gray-500">Certificates Issued</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Tenant Compliance --}}
        <div class="card p-5">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Tenant Compliance</h2>
            <div class="space-y-3">
                @forelse($tenantStats->take(8) as $tenant)
                    <div class="flex items-center gap-3">
                        <span class="text-sm text-gray-700 w-32 truncate" title="{{ $tenant->name }}">{{ $tenant->name }}</span>
                        <div class="flex-1 bg-gray-100 rounded-full h-2.5">
                            <div class="h-2.5 rounded-full {{ $tenant->completion_rate >= 80 ? 'bg-green-500' : ($tenant->completion_rate >= 50 ? 'bg-yellow-500' : 'bg-red-400') }}" style="width: {{ $tenant->completion_rate }}%"></div>
                        </div>
                        <span class="text-sm font-medium text-gray-600 w-12 text-right">{{ $tenant->completion_rate }}%</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-400">No tenant data yet.</p>
                @endforelse
            </div>
        </div>

        {{-- Course Popularity --}}
        <div class="card p-5">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Top Courses</h2>
            <div class="space-y-3">
                @forelse($courseStats->take(6) as $course)
                    <div class="flex items-center justify-between">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-700 truncate">{{ $course->title }}</p>
                            <p class="text-xs text-gray-400">{{ $course->total_enrollments }} enrolled</p>
                        </div>
                        <span class="text-sm font-semibold {{ $course->total_enrollments > 0 ? 'text-green-600' : 'text-gray-400' }}">
                            {{ $course->total_enrollments > 0 ? round(($course->completed_enrollments / $course->total_enrollments) * 100) : 0 }}%
                        </span>
                    </div>
                @empty
                    <p class="text-sm text-gray-400">No course data yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Recent Completions --}}
    <div class="card p-5">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Recent Completions</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead><tr class="text-left text-xs text-gray-500 uppercase">
                    <th class="pb-2">User</th><th class="pb-2">Course</th><th class="pb-2">Completed</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($recentCompletions as $enrollment)
                        <tr>
                            <td class="py-2 text-gray-700">{{ $enrollment->user->name ?? 'Unknown' }}</td>
                            <td class="py-2 text-gray-600">{{ $enrollment->course->title ?? 'Unknown' }}</td>
                            <td class="py-2 text-gray-400">{{ $enrollment->completed_at?->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="py-4 text-center text-gray-400">No completions yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="card p-5">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Quick Actions</h2>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('platform.tenants.create') }}" class="btn-primary text-sm">+ New Tenant</a>
            <a href="{{ route('platform.courses.index') }}" class="btn-outline text-sm">Manage Courses</a>
            <a href="{{ route('platform.users.index') }}" class="btn-outline text-sm">View All Users</a>
            <a href="{{ route('platform.settings.edit') }}" class="btn-outline text-sm">Settings</a>
        </div>
    </div>
</div>
@endsection
