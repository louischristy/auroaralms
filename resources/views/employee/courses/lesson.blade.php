@extends('layouts.app')
@section('title', $lesson->title . ' — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-gray-500">
        <a href="{{ route('learn.courses.index') }}" class="hover:text-gray-700">Courses</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('learn.courses.show', $course) }}" class="hover:text-gray-700">{{ $course->title }}</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-800 font-medium">{{ $lesson->title }}</span>
    </div>

    {{-- Lesson progress bar --}}
    <div class="card p-4">
        <div class="flex items-center gap-2 overflow-x-auto">
            @foreach($lessons as $i => $l)
                @php
                    $done = in_array($l->id, $completedLessonIds);
                    $isCurrent = $l->id === $lesson->id;
                @endphp
                <div class="flex items-center gap-1 flex-shrink-0">
                    <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-medium
                        {{ $done ? 'bg-green-500 text-white' : ($isCurrent ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-500') }}">
                        @if($done)
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        @else
                            {{ $i + 1 }}
                        @endif
                    </div>
                    @if($i < $lessons->count() - 1)
                        <div class="w-6 h-0.5 {{ $done ? 'bg-green-500' : 'bg-gray-200' }}"></div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    {{-- Lesson content --}}
    <div class="card">
        <div class="px-6 py-4 border-b border-gray-100">
            <h1 class="text-xl font-bold text-gray-900">{{ $lesson->title }}</h1>
            <p class="text-sm text-gray-400 mt-1">{{ $lesson->duration_minutes }} min &middot; {{ ucfirst($lesson->content_type) }}</p>
        </div>

        @if($lesson->content_type === 'video' && $lesson->video_url)
            <div class="aspect-video bg-black">
                <iframe src="{{ $lesson->video_url }}" class="w-full h-full" frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen></iframe>
            </div>
        @endif

        <div class="px-6 py-6 prose prose-sm max-w-none text-gray-700">
            {!! $lesson->content !!}
        </div>
    </div>

    {{-- Navigation --}}
    <div class="flex items-center justify-between">
        <div>
            @if($prevLesson)
                <a href="{{ route('learn.courses.lesson', [$course, $prevLesson]) }}"
                   class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    {{ $prevLesson->title }}
                </a>
            @endif
        </div>
        <div class="flex items-center gap-3">
            @if(!$isCompleted)
                <form method="POST" action="{{ route('learn.courses.complete-lesson', [$course, $lesson]) }}">
                    @csrf
                    <button type="submit" class="btn-primary text-sm">
                        Mark as Complete & Continue
                    </button>
                </form>
            @else
                <span class="inline-flex items-center gap-1 text-green-600 text-sm font-medium">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    Completed
                </span>
                @if($nextLesson)
                    <a href="{{ route('learn.courses.lesson', [$course, $nextLesson]) }}" class="btn-primary text-sm">
                        Next Lesson
                    </a>
                @else
                    <a href="{{ route('learn.courses.show', $course) }}" class="btn-primary text-sm">
                        Back to Course
                    </a>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection
