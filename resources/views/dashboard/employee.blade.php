@extends('layouts.app')
@section('title', 'Dashboard — ' . ($branding['platform_name'] ?? 'Auroara LMS'))
@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Welcome, {{ $user->name }}</h1>
        <p class="text-sm text-gray-500 mt-1">Continue your cybersecurity awareness training.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <a href="{{ route('learn.courses.index') }}" class="card p-6 hover:shadow-md transition-shadow group">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-primary/10 flex items-center justify-center group-hover:bg-primary/20 transition-colors">
                    <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
                <div>
                    <p class="font-semibold text-gray-900">My Courses</p>
                    <p class="text-sm text-gray-500">View assigned training</p>
                </div>
            </div>
        </a>
        <a href="{{ route('learn.policies.index') }}" class="card p-6 hover:shadow-md transition-shadow group">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-accent/10 flex items-center justify-center group-hover:bg-accent/20 transition-colors">
                    <svg class="w-6 h-6 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                </div>
                <div>
                    <p class="font-semibold text-gray-900">Policies</p>
                    <p class="text-sm text-gray-500">Review & acknowledge</p>
                </div>
            </div>
        </a>
    </div>
</div>
@endsection
