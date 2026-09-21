@extends('layouts.app')
@section('title', 'My Courses — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">My Courses</h1>
        <p class="text-sm text-gray-500 mt-1">Your assigned cybersecurity training courses.</p>
    </div>

    @if($courses->isEmpty())
        <div class="card p-12 text-center">
            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
            <p class="text-gray-400 text-lg">No courses assigned yet.</p>
            <p class="text-gray-400 text-sm mt-1">Your administrator will assign training courses to you.</p>
        </div>
    @else
        {{-- Group by category --}}
        @php
            $grouped = $courses->groupBy('category');
        @endphp

        @foreach($grouped as $category => $categoryCourses)
            <div>
                <h2 class="text-lg font-semibold text-gray-800 mb-3 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full" style="background: var(--color-primary)"></span>
                    {{ $category }}
                </h2>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($categoryCourses as $course)
                        @php
                            $enrollment = $enrollments[$course->id] ?? null;
                            // Calculate progress from actual completion data (enrollment record may be stale)
                            $courseLessons = $course->lessons_count ?? 0;
                            $completedLessonsCount = isset($completedLessonsMap[$course->id]) ? count($completedLessonsMap[$course->id]) : 0;
                            $hasQuiz = isset($courseQuizMap[$course->id]);
                            $quizPassedFlag = $quizPassedMap[$course->id] ?? false;
                            if ($courseLessons === 0 && !$hasQuiz) {
                                $calcProgress = 0;
                            } elseif ($hasQuiz) {
                                $lPart = $courseLessons > 0 ? ($completedLessonsCount / $courseLessons) * 80 : 80;
                                $qPart = $quizPassedFlag ? 20 : 0;
                                $calcProgress = (int) round($lPart + $qPart);
                            } else {
                                $calcProgress = $courseLessons > 0 ? (int) round(($completedLessonsCount / $courseLessons) * 100) : 0;
                            }
                            $progress = max($calcProgress, $enrollment?->progress_percent ?? 0);
                            $status = $progress >= 100 ? 'completed' : ($enrollment?->status ?? 'not_started');
                        @endphp
                        <a href="{{ route('learn.courses.show', $course) }}" class="card hover:shadow-md transition-shadow">
                            <div class="p-5">
                                <div class="flex items-start justify-between mb-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                        {{ $course->difficulty === 'beginner' ? 'bg-green-100 text-green-700' : ($course->difficulty === 'intermediate' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                                        {{ ucfirst($course->difficulty) }}
                                    </span>
                                    @if($status === 'completed')
                                        <span class="inline-flex items-center gap-1 text-green-600 text-xs font-medium">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                            Completed
                                        </span>
                                    @endif
                                </div>
                                <h3 class="font-semibold text-gray-900 mb-1">{{ $course->title }}</h3>
                                <p class="text-sm text-gray-500 line-clamp-2 mb-3">{{ $course->description }}</p>
                                <div class="flex items-center gap-3 text-xs text-gray-400 mb-3">
                                    <span>{{ $course->lessons_count }} lessons</span>
                                    <span>&middot;</span>
                                    <span>{{ $course->duration_minutes }} min</span>
                                </div>

                                {{-- Progress bar --}}
                                <div class="w-full bg-gray-100 rounded-full h-2">
                                    <div class="h-2 rounded-full transition-all duration-300 {{ $status === 'completed' ? 'bg-green-500' : '' }}"
                                         style="width: {{ $progress }}%; background-color: {{ $status !== 'completed' ? 'var(--color-primary)' : '' }}"></div>
                                </div>
                                <p class="text-xs text-gray-400 mt-1">{{ $progress }}% complete</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    @endif
</div>
@endsection
