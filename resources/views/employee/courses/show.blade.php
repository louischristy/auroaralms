@extends('layouts.app')
@section('title', 'Course — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">{{ $course->title ?? 'Course' }}</h1>
        <p class="text-sm text-gray-500 mt-1">Course content.</p>
    </div>

    <div class="card p-6">
        <p class="text-gray-400 text-center py-8">Course content coming soon.</p>
    </div>
</div>
@endsection
