@extends('layouts.app')
@section('title', $course->title . ' — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
@php
    $totalLessons = $course->lessons->count();
    $completedCount = count($completedLessonIds);
    $hasQuiz = $course->quiz !== null;
    $quizPassedForProgress = $quizPassed;

    // Calculate progress from actual data (not just enrollment record)
    if ($totalLessons === 0 && !$hasQuiz) {
        $calculatedProgress = 0;
    } elseif ($hasQuiz) {
        $lessonPart = $totalLessons > 0 ? ($completedCount / $totalLessons) * 80 : 80;
        $quizPart = $quizPassedForProgress ? 20 : 0;
        $calculatedProgress = (int) round($lessonPart + $quizPart);
    } else {
        $calculatedProgress = $totalLessons > 0 ? (int) round(($completedCount / $totalLessons) * 100) : 0;
    }

    // Use calculated progress (more reliable) or enrollment if higher
    $progressPercent = max($calculatedProgress, $enrollment->progress_percent ?? 0);
    $firstIncomplete = $course->lessons->first(fn($l) => !in_array($l->id, $completedLessonIds));
@endphp

<div class="max-w-4xl mx-auto space-y-6">
    {{-- Back link --}}
    <a href="{{ route('learn.courses.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-400 hover:text-gray-600 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to Courses
    </a>

    {{-- Course hero --}}
    <div class="card overflow-hidden">
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-6 py-8 text-white">
            <div class="flex items-start justify-between flex-wrap gap-4">
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $course->difficulty === 'beginner' ? 'bg-green-400/20 text-green-200' : ($course->difficulty === 'intermediate' ? 'bg-yellow-400/20 text-yellow-200' : 'bg-red-400/20 text-red-200') }}">
                            {{ ucfirst($course->difficulty) }}
                        </span>
                        @if($course->is_mandatory)
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-red-400/20 text-red-200">Required</span>
                        @endif
                        @if($course->category)
                            <span class="text-xs text-blue-200">{{ $course->category }}</span>
                        @endif
                    </div>
                    <h1 class="text-2xl font-bold mb-2">{{ $course->title }}</h1>
                    <p class="text-blue-100 text-sm leading-relaxed">{{ $course->description }}</p>

                    <div class="flex items-center gap-4 mt-4 text-blue-200 text-sm">
                        <span class="inline-flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            {{ $totalLessons }} {{ $totalLessons === 1 ? 'lesson' : 'lessons' }}
                        </span>
                        <span class="inline-flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ $course->duration_minutes }} min
                        </span>
                        @if($course->quiz)
                            <span class="inline-flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                Quiz included
                            </span>
                        @endif
                    </div>

                    @if($enrollment && $enrollment->due_date)
                        @php $daysLeft = now()->diffInDays($enrollment->due_date, false); @endphp
                        <div class="mt-3 inline-flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-full {{ $daysLeft < 0 ? 'bg-red-400/20 text-red-200' : ($daysLeft <= 7 ? 'bg-amber-400/20 text-amber-200' : 'bg-blue-400/20 text-blue-200') }}">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            @if($daysLeft < 0)
                                Overdue by {{ abs($daysLeft) }} {{ abs($daysLeft) === 1 ? 'day' : 'days' }}
                            @else
                                Due {{ $enrollment->due_date->format('M d, Y') }} ({{ $daysLeft }} {{ $daysLeft === 1 ? 'day' : 'days' }} left)
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Progress ring --}}
                <div class="text-center flex-shrink-0">
                    <div class="relative w-24 h-24">
                        <svg class="w-24 h-24 transform -rotate-90" viewBox="0 0 36 36">
                            <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                                  fill="none" stroke="rgba(255,255,255,0.15)" stroke-width="3"/>
                            <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                                  fill="none" stroke="{{ $progressPercent >= 100 ? '#34d399' : '#fff' }}" stroke-width="3"
                                  stroke-dasharray="{{ $progressPercent }}, 100" stroke-linecap="round"/>
                        </svg>
                        <span class="absolute inset-0 flex items-center justify-center text-xl font-bold">{{ $progressPercent }}%</span>
                    </div>
                    <p class="text-xs text-blue-200 mt-2">{{ $completedCount }}/{{ $totalLessons }} lessons</p>
                </div>
            </div>
        </div>

        {{-- CTA bar --}}
        @if($firstIncomplete)
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
            <p class="text-sm text-gray-500">
                @if($completedCount === 0)
                    Ready to begin? Start with the first lesson.
                @else
                    Pick up where you left off.
                @endif
            </p>
            <a href="{{ route('learn.courses.lesson', [$course, $firstIncomplete]) }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition shadow-sm">
                {{ $completedCount === 0 ? 'Start Learning' : 'Continue' }}
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </a>
        </div>
        @endif
    </div>

    {{-- Learning objectives --}}
    @if($course->objectives && count($course->objectives))
    <div class="card p-6">
        <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-3">What you'll learn</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            @foreach($course->objectives as $objective)
                <div class="flex items-start gap-2.5">
                    <div class="flex-shrink-0 w-5 h-5 bg-green-100 rounded-full flex items-center justify-center mt-0.5">
                        <svg class="w-3 h-3 text-green-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    </div>
                    <p class="text-sm text-gray-600">{{ $objective }}</p>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Lessons list --}}
    <div class="card">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900">Course Content</h2>
            <span class="text-xs text-gray-400">{{ $completedCount }}/{{ $totalLessons }} complete</span>
        </div>
        <div class="divide-y divide-gray-50">
            @foreach($course->lessons as $index => $lesson)
                @php
                    $isLessonCompleted = in_array($lesson->id, $completedLessonIds);
                    $isNextUp = $firstIncomplete && $lesson->id === $firstIncomplete->id;
                @endphp
                <a href="{{ route('learn.courses.lesson', [$course, $lesson]) }}"
                   class="flex items-center gap-4 px-6 py-4 hover:bg-gray-50 transition-colors group {{ $isNextUp ? 'bg-blue-50/50' : '' }}">
                    <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center text-sm font-semibold transition
                        {{ $isLessonCompleted ? 'bg-green-100 text-green-600' : ($isNextUp ? 'bg-blue-100 text-blue-600 ring-2 ring-blue-300' : 'bg-gray-100 text-gray-400 group-hover:bg-gray-200') }}">
                        @if($isLessonCompleted)
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        @else
                            {{ $index + 1 }}
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-sm font-medium text-gray-900 group-hover:text-blue-600 transition">{{ $lesson->title }}</h3>
                        <div class="flex items-center gap-3 text-xs text-gray-400 mt-0.5">
                            <span>{{ $lesson->duration_minutes }} min</span>
                            <span>{{ ucfirst($lesson->content_type) }}</span>
                        </div>
                    </div>
                    @if($isNextUp)
                        <span class="text-xs font-medium text-blue-600 bg-blue-100 px-2 py-0.5 rounded-full">Next</span>
                    @endif
                    <svg class="w-5 h-5 text-gray-300 group-hover:text-gray-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            @endforeach
        </div>
    </div>

    {{-- Quiz section --}}
    @if($course->quiz)
        <div class="card overflow-hidden">
            <div class="px-6 py-5 flex items-center justify-between {{ $quizPassed ? 'bg-green-50' : 'bg-gray-50' }} border-b border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center {{ $quizPassed ? 'bg-green-100 text-green-600' : 'bg-blue-100 text-blue-600' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                    </div>
                    <div>
                        <h2 class="text-base font-semibold text-gray-900">Final Assessment</h2>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Pass with {{ $course->passing_score }}% to earn your certificate.
                            @if($course->quiz->max_attempts > 0)
                                {{ $course->quiz->max_attempts }} attempts allowed.
                            @endif
                        </p>
                    </div>
                </div>
                <div>
                    @if($quizPassed)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-green-100 text-green-700 text-sm font-semibold">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            Passed
                        </span>
                    @else
                        <a href="{{ route('learn.courses.quiz', $course) }}"
                           class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition shadow-sm">
                            {{ $quizAttempts->isNotEmpty() ? 'Retry Quiz' : 'Take Quiz' }}
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    @endif
                </div>
            </div>

            @if($quizAttempts->isNotEmpty())
                <div class="px-6 py-4">
                    <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Your Attempts</h3>
                    <div class="space-y-2">
                        @foreach($quizAttempts as $attempt)
                            <a href="{{ route('learn.courses.quiz-result', [$course, $attempt]) }}"
                               class="flex items-center justify-between text-sm p-3 rounded-lg hover:bg-gray-50 transition border border-gray-100">
                                <span class="text-gray-600">{{ $attempt->created_at->format('M d, Y \a\t H:i') }}</span>
                                <span class="font-semibold {{ $attempt->passed ? 'text-green-600' : 'text-red-500' }}">
                                    {{ $attempt->score }}% ({{ $attempt->correct_count }}/{{ $attempt->total_questions }})
                                    &mdash; {{ $attempt->passed ? 'Passed' : 'Failed' }}
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
