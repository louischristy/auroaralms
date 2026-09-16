@extends('layouts.app')
@section('title', $course->title . ' — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    {{-- Back link --}}
    <a href="{{ route('learn.courses.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to Courses
    </a>

    {{-- Course header --}}
    <div class="card p-6">
        <div class="flex items-start justify-between flex-wrap gap-4">
            <div class="flex-1">
                <div class="flex items-center gap-2 mb-2">
                    <span class="text-xs font-medium px-2 py-0.5 rounded {{ $course->difficulty === 'beginner' ? 'bg-green-100 text-green-700' : ($course->difficulty === 'intermediate' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                        {{ ucfirst($course->difficulty) }}
                    </span>
                    <span class="text-xs text-gray-400">{{ $course->category }}</span>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ $course->title }}</h1>
                <p class="text-gray-600">{{ $course->description }}</p>

                @if($course->objectives)
                    <div class="mt-4">
                        <h3 class="text-sm font-semibold text-gray-700 mb-2">Learning Objectives</h3>
                        <ul class="space-y-1">
                            @foreach($course->objectives as $objective)
                                <li class="flex items-start gap-2 text-sm text-gray-600">
                                    <svg class="w-4 h-4 mt-0.5 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                    {{ $objective }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
            <div class="text-center">
                <div class="relative w-20 h-20">
                    <svg class="w-20 h-20 transform -rotate-90" viewBox="0 0 36 36">
                        <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                              fill="none" stroke="#e5e7eb" stroke-width="3"/>
                        <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                              fill="none" stroke="{{ $enrollment->status === 'completed' ? '#10b981' : 'var(--color-primary)' }}" stroke-width="3"
                              stroke-dasharray="{{ $enrollment->progress_percent }}, 100"/>
                    </svg>
                    <span class="absolute inset-0 flex items-center justify-center text-lg font-bold text-gray-800">{{ $enrollment->progress_percent }}%</span>
                </div>
                <p class="text-xs text-gray-400 mt-1">{{ $course->duration_minutes }} min</p>
            </div>
        </div>
    </div>

    {{-- Lessons list --}}
    <div class="card">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-lg font-semibold text-gray-900">Lessons</h2>
        </div>
        <div class="divide-y divide-gray-50">
            @foreach($course->lessons as $index => $lesson)
                @php
                    $isCompleted = in_array($lesson->id, $completedLessonIds);
                @endphp
                <a href="{{ route('learn.courses.lesson', [$course, $lesson]) }}"
                   class="flex items-center gap-4 px-6 py-4 hover:bg-gray-50 transition-colors">
                    <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium
                        {{ $isCompleted ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-500' }}">
                        @if($isCompleted)
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        @else
                            {{ $index + 1 }}
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-sm font-medium text-gray-900">{{ $lesson->title }}</h3>
                        <p class="text-xs text-gray-400">{{ $lesson->duration_minutes }} min &middot; {{ ucfirst($lesson->content_type) }}</p>
                    </div>
                    <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            @endforeach
        </div>
    </div>

    {{-- Quiz section --}}
    @if($course->quiz)
        <div class="card p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Course Quiz</h2>
                    <p class="text-sm text-gray-500 mt-1">
                        Pass with {{ $course->passing_score }}% or higher to complete the course.
                        @if($course->quiz->max_attempts > 0)
                            {{ $course->quiz->max_attempts }} attempts allowed.
                        @endif
                    </p>
                </div>
                <div>
                    @if($quizPassed)
                        <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-green-100 text-green-700 text-sm font-medium">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            Passed
                        </span>
                    @else
                        <a href="{{ route('learn.courses.quiz', $course) }}" class="btn-primary text-sm">
                            {{ $quizAttempts->isNotEmpty() ? 'Retry Quiz' : 'Take Quiz' }}
                        </a>
                    @endif
                </div>
            </div>

            @if($quizAttempts->isNotEmpty())
                <div class="mt-4 border-t border-gray-100 pt-4">
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Previous Attempts</h3>
                    <div class="space-y-2">
                        @foreach($quizAttempts as $attempt)
                            <a href="{{ route('learn.courses.quiz-result', [$course, $attempt]) }}"
                               class="flex items-center justify-between text-sm p-2 rounded hover:bg-gray-50">
                                <span class="text-gray-600">{{ $attempt->created_at->format('M d, Y H:i') }}</span>
                                <span class="font-medium {{ $attempt->passed ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $attempt->score }}% ({{ $attempt->correct_count }}/{{ $attempt->total_questions }})
                                    — {{ $attempt->passed ? 'Passed' : 'Failed' }}
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>
@endsection
