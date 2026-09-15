@extends('layouts.app')
@section('title', 'Dashboard — ' . ($branding['platform_name'] ?? 'Auroara LMS'))
@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Team Dashboard</h1>
        <p class="text-sm text-gray-500 mt-1">Monitor your team's training progress.</p>
    </div>
    <div class="card p-5">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg bg-primary/10 flex items-center justify-center">
                <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['team_size'] }}</p>
                <p class="text-sm text-gray-500">Team Members</p>
            </div>
        </div>
    </div>
    <div class="card p-5">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Quick Actions</h2>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('team.members') }}" class="btn-primary">View Team</a>
            <a href="{{ route('learn.courses.index') }}" class="btn-outline">My Courses</a>
        </div>
    </div>
</div>
@endsection
