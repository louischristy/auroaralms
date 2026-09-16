@extends('layouts.app')
@section('title', 'Dashboard — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Organization Dashboard</h1>
        <p class="text-sm text-gray-500 mt-1">Training compliance and user progress overview.</p>
    </div>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="card p-4">
            <p class="text-2xl font-bold text-gray-900">{{ $stats['total_users'] }}</p>
            <p class="text-xs text-gray-500">Users</p>
        </div>
        <div class="card p-4">
            <p class="text-2xl font-bold {{ $stats['completion_rate'] >= 80 ? 'text-green-600' : ($stats['completion_rate'] >= 50 ? 'text-yellow-600' : 'text-red-600') }}">{{ $stats['completion_rate'] }}%</p>
            <p class="text-xs text-gray-500">Course Completion</p>
        </div>
        <div class="card p-4">
            <p class="text-2xl font-bold {{ $stats['policy_compliance'] >= 80 ? 'text-green-600' : 'text-yellow-600' }}">{{ $stats['policy_compliance'] }}%</p>
            <p class="text-xs text-gray-500">Policy Compliance</p>
        </div>
        <div class="card p-4">
            <p class="text-2xl font-bold {{ $stats['overdue_count'] > 0 ? 'text-red-600' : 'text-green-600' }}">{{ $stats['overdue_count'] }}</p>
            <p class="text-xs text-gray-500">Overdue</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Department Progress --}}
        <div class="card p-5">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Department Completion Rates</h2>
            <div class="space-y-3">
                @forelse($departments as $dept)
                    <div class="flex items-center gap-3">
                        <span class="text-sm text-gray-700 w-32 truncate">{{ $dept->name }}</span>
                        <div class="flex-1 bg-gray-100 rounded-full h-2.5">
                            <div class="h-2.5 rounded-full {{ $dept->completion_rate >= 80 ? 'bg-green-500' : ($dept->completion_rate >= 50 ? 'bg-yellow-500' : 'bg-red-400') }}" style="width: {{ $dept->completion_rate }}%"></div>
                        </div>
                        <span class="text-sm font-medium text-gray-600 w-12 text-right">{{ $dept->completion_rate }}%</span>
                        <span class="text-xs text-gray-400 w-16">{{ $dept->users_count }} users</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-400">No departments yet.</p>
                @endforelse
            </div>
        </div>

        {{-- Users Needing Attention --}}
        <div class="card p-5">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Needs Attention</h2>
            <div class="space-y-3">
                @forelse($usersNeedingAttention->take(6) as $u)
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-700">{{ $u->name }}</p>
                            <p class="text-xs text-gray-400">{{ $u->department->name ?? 'No dept' }} · {{ $u->courseEnrollments->count() }} pending</p>
                        </div>
                        @if($u->courseEnrollments->contains(fn($e) => $e->isOverdue()))
                            <span class="px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-700">Overdue</span>
                        @else
                            <span class="px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-700">Not Started</span>
                        @endif
                    </div>
                @empty
                    <div class="text-center py-4">
                        <p class="text-sm text-green-600 font-medium">All users on track!</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="card p-5">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Quick Actions</h2>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('manage.users.create') }}" class="btn-primary text-sm">+ Add User</a>
            <a href="{{ route('manage.policies.create') }}" class="btn-outline text-sm">+ Create Policy</a>
            <a href="{{ route('manage.departments.create') }}" class="btn-outline text-sm">+ Department</a>
        </div>
    </div>
</div>
@endsection
