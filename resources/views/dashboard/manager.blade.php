@extends('layouts.app')
@section('title', 'Team Dashboard — ' . ($branding['platform_name'] ?? 'Auroara LMS'))
@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Team Dashboard</h1>
        <p class="text-sm text-gray-500 mt-1">Monitor your team's training progress.</p>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="card p-4">
            <p class="text-2xl font-bold text-gray-900">{{ $stats['team_size'] }}</p>
            <p class="text-xs text-gray-500">Team Members</p>
        </div>
        <div class="card p-4">
            <p class="text-2xl font-bold text-gray-900">{{ $stats['enrolled'] }}</p>
            <p class="text-xs text-gray-500">Enrollments</p>
        </div>
        <div class="card p-4">
            <p class="text-2xl font-bold text-green-600">{{ $stats['completed'] }}</p>
            <p class="text-xs text-gray-500">Completed</p>
        </div>
        <div class="card p-4">
            <p class="text-2xl font-bold {{ $stats['completion_rate'] >= 80 ? 'text-green-600' : 'text-yellow-600' }}">{{ $stats['completion_rate'] }}%</p>
            <p class="text-xs text-gray-500">Completion Rate</p>
        </div>
    </div>

    {{-- Team Member Progress --}}
    <div class="card p-5">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Team Member Progress</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead><tr class="text-left text-xs text-gray-500 uppercase">
                    <th class="pb-2">Name</th><th class="pb-2">Enrolled</th><th class="pb-2">Completed</th><th class="pb-2">Progress</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($teamMembers as $member)
                        <tr>
                            <td class="py-2.5 text-gray-700 font-medium">{{ $member->name }}</td>
                            <td class="py-2.5 text-gray-600">{{ $member->course_enrollments_count }}</td>
                            <td class="py-2.5 text-gray-600">{{ $member->completed_courses_count }}</td>
                            <td class="py-2.5">
                                <div class="flex items-center gap-2">
                                    <div class="w-24 bg-gray-100 rounded-full h-2">
                                        <div class="h-2 rounded-full {{ $member->completion_rate >= 80 ? 'bg-green-500' : ($member->completion_rate >= 50 ? 'bg-yellow-500' : 'bg-red-400') }}" style="width: {{ $member->completion_rate }}%"></div>
                                    </div>
                                    <span class="text-xs font-medium text-gray-500">{{ $member->completion_rate }}%</span>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="card p-5">
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('team.members') }}" class="btn-primary text-sm">View Full Team</a>
            <a href="{{ route('learn.courses.index') }}" class="btn-outline text-sm">My Courses</a>
        </div>
    </div>
</div>
@endsection
