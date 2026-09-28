@extends('layouts.app')
@section('title', 'Thank You — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="max-w-lg mx-auto">
    <div class="card p-10 text-center">
        <div class="mx-auto w-16 h-16 rounded-full bg-green-100 flex items-center justify-center mb-5">
            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-900">Thank you!</h1>
        <p class="text-gray-600 mt-2">Your response to <span class="font-medium">{{ $survey->title }}</span> has been recorded.</p>
        <div class="mt-6 flex justify-center gap-3">
            <a href="{{ route('learn.surveys.index') }}" class="btn-primary">More Surveys</a>
            <a href="{{ route('learn.courses.index') }}" class="btn-outline">My Courses</a>
        </div>
    </div>
</div>
@endsection
