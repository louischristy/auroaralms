@extends('layouts.app')
@section('title', 'Dashboard — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Welcome back, {{ Auth::user()->name }}</h1>
        <p class="text-sm text-gray-500 mt-1">Manage your organization's training and compliance.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="card p-5">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-primary/10 flex items-center justify-center">
                    <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_users'] }}</p>
                    <p class="text-sm text-gray-500">Users</p>
                </div>
            </div>
        </div>
        <div class="card p-5">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-secondary/10 flex items-center justify-center">
                    <svg class="w-6 h-6 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6z"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_departments'] }}</p>
                    <p class="text-sm text-gray-500">Departments</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card p-5">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Quick Actions</h2>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('manage.users.create') }}" class="btn-primary">+ Add User</a>
            <a href="{{ route('manage.departments.create') }}" class="btn-outline">+ Add Department</a>
            <a href="{{ route('manage.policies.create') }}" class="btn-outline">+ Create Policy</a>
        </div>
    </div>
</div>
@endsection
