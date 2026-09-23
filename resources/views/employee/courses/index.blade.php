@extends('layouts.app')
@section('title', 'My Courses — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
@php
    $categoryColors = [
        'Phishing & Email Security' => ['border' => 'border-red-500', 'bg' => 'bg-red-50', 'text' => 'text-red-700', 'ring' => 'stroke-red-500'],
        'Social Engineering' => ['border' => 'border-orange-500', 'bg' => 'bg-orange-50', 'text' => 'text-orange-700', 'ring' => 'stroke-orange-500'],
        'Password & Authentication' => ['border' => 'border-amber-500', 'bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'ring' => 'stroke-amber-500'],
        'Data Protection & Privacy' => ['border' => 'border-violet-500', 'bg' => 'bg-violet-50', 'text' => 'text-violet-700', 'ring' => 'stroke-violet-500'],
        'Malware & Ransomware' => ['border' => 'border-rose-500', 'bg' => 'bg-rose-50', 'text' => 'text-rose-700', 'ring' => 'stroke-rose-500'],
        'Mobile & Remote Work Security' => ['border' => 'border-cyan-500', 'bg' => 'bg-cyan-50', 'text' => 'text-cyan-700', 'ring' => 'stroke-cyan-500'],
        'Physical Security & Workplace Safety' => ['border' => 'border-teal-500', 'bg' => 'bg-teal-50', 'text' => 'text-teal-700', 'ring' => 'stroke-teal-500'],
        'Incident Response & Compliance' => ['border' => 'border-blue-500', 'bg' => 'bg-blue-50', 'text' => 'text-blue-700', 'ring' => 'stroke-blue-500'],
        'Organization Specific' => ['border' => 'border-emerald-500', 'bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'ring' => 'stroke-emerald-500'],
    ];
    $defaultColor = ['border' => 'border-gray-400', 'bg' => 'bg-gray-50', 'text' => 'text-gray-700', 'ring' => 'stroke-gray-400'];
    $allCategories = $courses->pluck('category')->unique()->filter()->sort()->values();

    // Pre-compute stats
    $totalCourses = $courses->count();
    $completedCount = 0;
    $inProgressCount = 0;
    $progressSum = 0;
@endphp

<div x-data="{ search: '', category: 'all', status: 'all', view: 'grid' }" class="space-y-6">

    {{-- Page Header --}}
    <div>
        <h1 class="text-2xl font-bold text-gray-900">My Courses</h1>
        <p class="text-sm text-gray-500 mt-1">Your assigned cybersecurity training courses</p>
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

    {{-- Stats Summary --}}
    @php
        // Pre-compute all course data
        $courseData = [];
        foreach ($courses as $course) {
            $enrollment = $enrollments[$course->id] ?? null;
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
            $cStatus = $progress >= 100 ? 'completed' : ($progress > 0 || $enrollment ? 'in-progress' : 'not-started');

            $courseData[$course->id] = compact('enrollment', 'courseLessons', 'completedLessonsCount', 'hasQuiz', 'quizPassedFlag', 'progress', 'cStatus');
            $progressSum += $progress;
            if ($cStatus === 'completed') $completedCount++;
            elseif ($cStatus === 'in-progress') $inProgressCount++;
        }
        $avgProgress = $totalCourses > 0 ? (int) round($progressSum / $totalCourses) : 0;
    @endphp

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="card p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Total</p>
                <p class="text-xl font-bold text-gray-900">{{ $totalCourses }}</p>
            </div>
        </div>
        <div class="card p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">In Progress</p>
                <p class="text-xl font-bold text-gray-900">{{ $inProgressCount }}</p>
            </div>
        </div>
        <div class="card p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Completed</p>
                <p class="text-xl font-bold text-gray-900">{{ $completedCount }}</p>
            </div>
        </div>
        <div class="card p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-violet-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Avg Progress</p>
                <p class="text-xl font-bold text-gray-900">{{ $avgProgress }}%</p>
            </div>
        </div>
    </div>

    {{-- Search / Filter / View Toggle --}}
    <div class="card p-4">
        <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center">
            <div class="relative flex-1 w-full sm:w-auto">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" x-model="search" placeholder="Search courses..." class="input w-full pl-10">
            </div>
            <select x-model="category" class="input w-full sm:w-auto sm:min-w-[180px]">
                <option value="all">All Categories</option>
                @foreach($allCategories as $cat)
                    <option value="{{ $cat }}">{{ $cat }}</option>
                @endforeach
            </select>
            <select x-model="status" class="input w-full sm:w-auto sm:min-w-[140px]">
                <option value="all">All Status</option>
                <option value="in-progress">In Progress</option>
                <option value="completed">Completed</option>
                <option value="not-started">Not Started</option>
            </select>
            <div class="flex items-center border border-gray-200 rounded-lg overflow-hidden ml-auto flex-shrink-0">
                <button x-on:click="view = 'grid'" class="p-2 transition-colors" x-bind:class="view === 'grid' ? 'bg-gray-800 text-white' : 'text-gray-400 hover:bg-gray-100'" title="Grid view">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                </button>
                <button x-on:click="view = 'list'" class="p-2 transition-colors" x-bind:class="view === 'list' ? 'bg-gray-800 text-white' : 'text-gray-400 hover:bg-gray-100'" title="List view">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Grid View --}}
    <div x-show="view === 'grid'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($courses as $course)
            @php
                $cd = $courseData[$course->id];
                $colors = $categoryColors[$course->category] ?? $defaultColor;
                $circumference = 2 * 3.14159 * 18;
                $offset = $circumference - ($cd['progress'] / 100) * $circumference;
            @endphp
            <a href="{{ route('learn.courses.show', $course) }}"
               data-course
               data-title="{{ Str::lower($course->title) }}"
               data-category="{{ $course->category }}"
               data-status="{{ $cd['cStatus'] }}"
               x-show="$el.dataset.title.includes(search.toLowerCase()) && (category === 'all' || $el.dataset.category === category) && (status === 'all' || $el.dataset.status === status)"
               class="card border-t-4 {{ $colors['border'] }} hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 group block">
                <div class="p-5">
                    {{-- Category + Difficulty --}}
                    <div class="flex items-center justify-between mb-3">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium {{ $colors['bg'] }} {{ $colors['text'] }}">
                            {{ $course->category }}
                        </span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                            {{ $course->difficulty === 'beginner' ? 'bg-green-100 text-green-700' : ($course->difficulty === 'intermediate' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                            {{ ucfirst($course->difficulty) }}
                        </span>
                    </div>

                    {{-- Title --}}
                    <h3 class="font-semibold text-gray-900 mb-1 line-clamp-2 group-hover:text-[color:var(--color-primary)] transition-colors">{{ $course->title }}</h3>
                    <p class="text-sm text-gray-500 line-clamp-2 mb-4">{{ $course->description }}</p>

                    {{-- Meta --}}
                    <div class="flex items-center gap-3 text-xs text-gray-400 mb-4">
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            {{ $cd['courseLessons'] }} lessons
                        </span>
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ $course->duration_minutes }} min
                        </span>
                        @if($cd['hasQuiz'])
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Quiz
                        </span>
                        @endif
                    </div>

                    {{-- Progress Ring + Status --}}
                    <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                        <div class="flex items-center gap-3">
                            <div class="relative w-12 h-12">
                                <svg class="w-12 h-12 -rotate-90" viewBox="0 0 40 40">
                                    <circle cx="20" cy="20" r="18" fill="none" stroke-width="3" class="stroke-gray-200"/>
                                    <circle cx="20" cy="20" r="18" fill="none" stroke-width="3" stroke-linecap="round"
                                        class="{{ $cd['cStatus'] === 'completed' ? 'stroke-emerald-500' : $colors['ring'] }}"
                                        stroke-dasharray="{{ $circumference }}"
                                        stroke-dashoffset="{{ $offset }}"/>
                                </svg>
                                <span class="absolute inset-0 flex items-center justify-center text-xs font-bold text-gray-700">{{ $cd['progress'] }}%</span>
                            </div>
                            <div class="text-xs text-gray-500">
                                <span class="font-medium text-gray-700">{{ $cd['completedLessonsCount'] }}/{{ $cd['courseLessons'] }}</span> lessons
                            </div>
                        </div>
                        @if($cd['cStatus'] === 'completed')
                            <span class="inline-flex items-center gap-1 text-emerald-600 text-xs font-semibold bg-emerald-50 px-2.5 py-1 rounded-full">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                Done
                            </span>
                        @elseif($cd['cStatus'] === 'in-progress')
                            <span class="text-xs font-medium text-white px-3 py-1 rounded-full" style="background: var(--color-primary)">Continue</span>
                        @else
                            <span class="text-xs font-medium text-gray-600 bg-gray-100 px-3 py-1 rounded-full">Start</span>
                        @endif
                    </div>
                </div>
            </a>
        @endforeach
    </div>

    {{-- List View --}}
    <div x-show="view === 'list'" class="card overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase hidden sm:table-cell">Category</th>
                    <th class="px-5 py-3 text-center text-xs font-medium text-gray-500 uppercase hidden md:table-cell">Difficulty</th>
                    <th class="px-5 py-3 text-center text-xs font-medium text-gray-500 uppercase hidden md:table-cell">Lessons</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Progress</th>
                    <th class="px-5 py-3 text-right text-xs font-medium text-gray-500 uppercase">Action</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @foreach($courses as $course)
                    @php $cd = $courseData[$course->id]; $colors = $categoryColors[$course->category] ?? $defaultColor; @endphp
                    <tr data-course
                        data-title="{{ Str::lower($course->title) }}"
                        data-category="{{ $course->category }}"
                        data-status="{{ $cd['cStatus'] }}"
                        x-show="$el.dataset.title.includes(search.toLowerCase()) && (category === 'all' || $el.dataset.category === category) && (status === 'all' || $el.dataset.status === status)"
                        class="hover:bg-gray-50 transition-colors">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-1 h-8 rounded-full {{ str_replace('border-', 'bg-', $colors['border']) }}"></div>
                                <div>
                                    <a href="{{ route('learn.courses.show', $course) }}" class="font-medium text-gray-900 hover:text-[color:var(--color-primary)]">{{ $course->title }}</a>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $course->duration_minutes }} min</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4 hidden sm:table-cell">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium {{ $colors['bg'] }} {{ $colors['text'] }}">{{ $course->category }}</span>
                        </td>
                        <td class="px-5 py-4 text-center hidden md:table-cell">
                            <span class="text-xs font-medium {{ $course->difficulty === 'beginner' ? 'text-green-600' : ($course->difficulty === 'intermediate' ? 'text-yellow-600' : 'text-red-600') }}">{{ ucfirst($course->difficulty) }}</span>
                        </td>
                        <td class="px-5 py-4 text-center hidden md:table-cell text-sm text-gray-600">{{ $cd['completedLessonsCount'] }}/{{ $cd['courseLessons'] }}</td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2">
                                <div class="w-20 bg-gray-100 rounded-full h-1.5">
                                    <div class="h-1.5 rounded-full transition-all duration-300 {{ $cd['cStatus'] === 'completed' ? 'bg-emerald-500' : '' }}"
                                         style="width: {{ $cd['progress'] }}%; {{ $cd['cStatus'] !== 'completed' ? 'background-color: var(--color-primary)' : '' }}"></div>
                                </div>
                                <span class="text-xs font-medium text-gray-600 w-8">{{ $cd['progress'] }}%</span>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-right">
                            @if($cd['cStatus'] === 'completed')
                                <a href="{{ route('learn.courses.show', $course) }}" class="text-emerald-600 hover:text-emerald-800 text-xs font-medium">Review</a>
                            @elseif($cd['cStatus'] === 'in-progress')
                                <a href="{{ route('learn.courses.show', $course) }}" class="btn-primary text-xs px-3 py-1">Continue</a>
                            @else
                                <a href="{{ route('learn.courses.show', $course) }}" class="btn-outline text-xs px-3 py-1">Start</a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @endif
</div>
@endsection
