@extends('layouts.app')
@section('title', 'My Courses — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">My Courses</h1>
        <p class="text-sm text-gray-500 mt-1">Your assigned training courses.</p>
    </div>

    <div class="card p-6">
        @forelse($courses as $course)
            <div class="border-b border-gray-100 last:border-0 py-3 flex items-center justify-between">
                <span class="font-medium text-gray-900">{{ $course->title }}</span>
                <a href="{{ route('learn.courses.show', $course) }}" class="text-secondary hover:text-primary text-xs font-medium">View</a>
            </div>
        @empty
            <p class="text-center text-gray-400 py-8">No courses assigned yet.</p>
        @endforelse
    </div>
</div>
@endsection
