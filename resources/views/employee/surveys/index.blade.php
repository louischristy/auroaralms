@extends('layouts.app')
@section('title', 'Surveys — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Surveys</h1>
        <p class="text-sm text-gray-500 mt-1">Your feedback helps us improve training.</p>
    </div>

    @if($surveys->isEmpty())
        <div class="card p-10 text-center text-gray-400">
            <svg class="mx-auto w-10 h-10 mb-3 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            You're all caught up. No pending surveys.
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($surveys as $survey)
                <div class="card p-5 flex flex-col">
                    <div class="flex flex-wrap items-center gap-2 mb-2">
                        <span class="badge-info">{{ ucwords(str_replace('_', ' ', $survey->type)) }}</span>
                        @if($survey->is_required)<span class="badge bg-orange-100 text-orange-800">Required</span>@endif
                        @if($survey->is_anonymous)<span class="badge bg-gray-100 text-gray-600">Anonymous</span>@endif
                    </div>
                    <h3 class="text-base font-semibold text-gray-900">{{ $survey->title }}</h3>
                    @if($survey->course)
                        <p class="text-sm text-gray-500 mt-1 flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            {{ $survey->course->title }}
                        </p>
                    @endif
                    @if($survey->description)
                        <p class="text-sm text-gray-500 mt-2">{{ \Illuminate\Support\Str::limit($survey->description, 120) }}</p>
                    @endif
                    <div class="mt-auto pt-4 flex items-center justify-between">
                        <span class="text-xs text-gray-400">{{ $survey->questions_count }} {{ \Illuminate\Support\Str::plural('question', $survey->questions_count) }}</span>
                        <a href="{{ route('learn.surveys.show', $survey->id) }}" class="btn-primary btn-sm">Start Survey</a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @if($completed->isNotEmpty())
        <div>
            <h2 class="text-sm font-semibold uppercase tracking-wider text-gray-400 mb-3">Completed</h2>
            <div class="card divide-y divide-gray-100">
                @foreach($completed as $survey)
                    <div class="px-5 py-3 flex items-center justify-between text-sm">
                        <span class="text-gray-700">{{ $survey->title }}</span>
                        <span class="inline-flex items-center gap-1 text-green-600 text-xs font-medium">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Submitted
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
