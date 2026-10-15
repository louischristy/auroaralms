@extends('layouts.app')
@section('title', __('My Courses') . ' — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="space-y-8">
    {{-- Hero header --}}
    <div class="bg-gradient-to-r from-indigo-600 via-blue-600 to-blue-700 rounded-2xl px-8 py-10 text-white">
        <h1 class="text-3xl font-bold">{{ __('My Courses') }}</h1>
        <p class="text-blue-100 mt-2 text-sm max-w-xl">{{ __('Continue learning to strengthen your security skills and earn badges along the way.') }}</p>

        {{-- Quick stats --}}
        @php
            $totalCourses = 0;
            $completedCourses = 0;
            foreach ($coursesByCategory as $cat => $courses) {
                foreach ($courses as $c) {
                    $totalCourses++;
                    $cLessons = $c->lessons->count();
                    $cCompleted = count($completedLessonsMap[$c->id] ?? []);
                    $cQuizPassed = $quizPassedMap[$c->id] ?? false;
                    $hasQuiz = $c->quiz !== null;
                    if ($cLessons > 0 && $cCompleted >= $cLessons && (!$hasQuiz || $cQuizPassed)) {
                        $completedCourses++;
                    }
                }
            }
        @endphp
        <div class="flex items-center gap-6 mt-6">
            <div class="bg-white/10 backdrop-blur rounded-xl px-5 py-3">
                <p class="text-2xl font-bold">{{ $totalCourses }}</p>
                <p class="text-xs text-blue-200">{{ __('Total Courses') }}</p>
            </div>
            <div class="bg-white/10 backdrop-blur rounded-xl px-5 py-3">
                <p class="text-2xl font-bold">{{ $completedCourses }}</p>
                <p class="text-xs text-blue-200">{{ __('Completed') }}</p>
            </div>
            <div class="bg-white/10 backdrop-blur rounded-xl px-5 py-3">
                <p class="text-2xl font-bold">{{ $totalCourses - $completedCourses }}</p>
                <p class="text-xs text-blue-200">{{ __('In Progress') }}</p>
            </div>
        </div>
    </div>

    {{-- Category filter --}}
    <div class="flex items-center gap-3 overflow-x-auto pb-1">
        <a href="{{ route('learn.courses.index') }}"
           class="flex-shrink-0 px-4 py-2 rounded-full text-sm font-medium transition {{ !request('category') ? 'bg-indigo-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
            {{ __('All Categories') }}
        </a>
        @foreach($categories as $cat)
            <a href="{{ route('learn.courses.index', ['category' => $cat]) }}"
               class="flex-shrink-0 px-4 py-2 rounded-full text-sm font-medium transition {{ request('category') === $cat ? 'bg-indigo-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                {{ $cat }}
            </a>
        @endforeach
    </div>

    {{-- Course grid by category --}}
    @forelse($coursesByCategory as $category => $courses)
        <div>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-gray-900">{{ $category }}</h2>
                <span class="text-xs text-gray-400">{{ $courses->count() }} {{ $courses->count() === 1 ? __('course') : __('courses') }}</span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($courses as $course)
                    @php
                        $cLessons = $course->lessons->count();
                        $cCompleted = count($completedLessonsMap[$course->id] ?? []);
                        $cQuizPassed = $quizPassedMap[$course->id] ?? false;
                        $hasQuiz = $course->quiz !== null;
                        if ($cLessons === 0 && !$hasQuiz) {
                            $progress = 0;
                        } elseif ($hasQuiz) {
                            $lessonPart = $cLessons > 0 ? ($cCompleted / $cLessons) * 80 : 80;
                            $quizPart = $cQuizPassed ? 20 : 0;
                            $progress = (int) round($lessonPart + $quizPart);
                        } else {
                            $progress = $cLessons > 0 ? (int) round(($cCompleted / $cLessons) * 100) : 0;
                        }
                        $isComplete = $progress >= 100;
                        $diffColors = [
                            'beginner' => 'bg-emerald-100 text-emerald-700',
                            'intermediate' => 'bg-amber-100 text-amber-700',
                            'advanced' => 'bg-rose-100 text-rose-700',
                        ];
                    @endphp
                    <a href="{{ route('learn.courses.show', $course) }}"
                       class="group card overflow-hidden hover:shadow-lg transition-all duration-300 hover:-translate-y-0.5 flex flex-col">
                        {{-- Color bar --}}
                        <div class="h-1.5 {{ $isComplete ? 'bg-emerald-500' : ($progress > 0 ? 'bg-indigo-500' : 'bg-gray-200') }}"></div>

                        <div class="p-5 flex-1 flex flex-col">
                            {{-- Badges row --}}
                            <div class="flex items-center gap-2 mb-3">
                                <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full {{ $diffColors[$course->difficulty] ?? 'bg-gray-100 text-gray-600' }}">
                                    {{ ucfirst($course->difficulty) }}
                                </span>
                                @if($course->is_mandatory)
                                    <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-red-100 text-red-700">{{ __('Required') }}</span>
                                @endif
                                @if($isComplete)
                                    <span class="ml-auto text-xs font-semibold px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-700 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                        {{ __('Complete') }}
                                    </span>
                                @endif
                            </div>

                            {{-- Title & description --}}
                            <h3 class="font-semibold text-gray-900 group-hover:text-indigo-600 transition line-clamp-2">{{ $course->title }}</h3>
                            <p class="text-sm text-gray-500 mt-1.5 line-clamp-2 flex-1">{{ $course->description }}</p>

                            {{-- Meta --}}
                            <div class="flex items-center gap-4 mt-4 text-xs text-gray-400">
                                <span class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                    {{ $cLessons }} {{ __('lessons') }}
                                </span>
                                <span class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $course->duration_minutes }} {{ __('min') }}
                                </span>
                                @if($hasQuiz)
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                        {{ __('Quiz') }}
                                    </span>
                                @endif
                            </div>

                            {{-- Progress bar --}}
                            <div class="mt-4">
                                <div class="flex items-center justify-between text-xs mb-1.5">
                                    <span class="text-gray-500">{{ $cCompleted }}/{{ $cLessons }} {{ __('lessons') }}</span>
                                    <span class="font-semibold {{ $isComplete ? 'text-emerald-600' : 'text-indigo-600' }}">{{ $progress }}%</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-2">
                                    <div class="{{ $isComplete ? 'bg-emerald-500' : 'bg-indigo-500' }} h-2 rounded-full transition-all" style="width: {{ $progress }}%"></div>
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @empty
        <div class="card p-12 text-center">
            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            <p class="text-gray-500">{{ __('No courses available yet. Check back soon!') }}</p>
        </div>
    @endforelse
</div>
@endsection
