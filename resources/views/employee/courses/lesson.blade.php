@extends('layouts.app')
@section('title', $lesson->title . ' — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
@php
    $totalLessons = $lessons->count();
    $completedCount = count($completedLessonIds);
    $currentIndex = $lessons->search(fn($l) => $l->id === $lesson->id);
    $progressPercent = $totalLessons > 0 ? round(($completedCount / $totalLessons) * 100) : 0;
    $lessonNumber = $currentIndex !== false ? $currentIndex + 1 : 1;

    // Motivational messages
    $messages = [
        [0, 15, 'Great start! Every expert was once a beginner.'],
        [15, 40, 'You\'re building momentum — keep going!'],
        [40, 60, 'Halfway there! You\'re doing amazing.'],
        [60, 85, 'Almost there — the finish line is in sight!'],
        [85, 100, 'Final stretch! You\'ve got this!'],
        [100, 101, 'All lessons complete — well done!'],
    ];
    $motivationalMsg = '';
    foreach ($messages as $m) {
        if ($progressPercent >= $m[0] && $progressPercent < $m[1]) {
            $motivationalMsg = $m[2];
            break;
        }
    }
@endphp

<div class="max-w-4xl mx-auto pb-24">
    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-gray-400 mb-4">
        <a href="{{ route('learn.courses.index') }}" class="hover:text-gray-600 transition">Courses</a>
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('learn.courses.show', $course) }}" class="hover:text-gray-600 transition">{{ $course->title }}</a>
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-600 font-medium">Lesson {{ $lessonNumber }}</span>
    </div>

    {{-- Progress hero --}}
    <div class="card mb-6 overflow-hidden">
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-6 py-5 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm font-medium">Lesson {{ $lessonNumber }} of {{ $totalLessons }}</p>
                    <h1 class="text-xl font-bold mt-1">{{ $lesson->title }}</h1>
                    <div class="flex items-center gap-4 mt-2 text-blue-200 text-sm">
                        <span class="inline-flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ $lesson->duration_minutes }} min read
                        </span>
                        <span class="inline-flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            {{ ucfirst($lesson->content_type) }}
                        </span>
                        @if($isCompleted)
                            <span class="inline-flex items-center gap-1 text-green-300">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                Completed
                            </span>
                        @endif
                    </div>
                </div>
                <div class="hidden sm:block text-right">
                    <div class="relative w-16 h-16">
                        <svg class="w-16 h-16 transform -rotate-90" viewBox="0 0 36 36">
                            <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                                  fill="none" stroke="rgba(255,255,255,0.2)" stroke-width="3"/>
                            <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                                  fill="none" stroke="#fff" stroke-width="3"
                                  stroke-dasharray="{{ $progressPercent }}, 100"/>
                        </svg>
                        <span class="absolute inset-0 flex items-center justify-center text-sm font-bold">{{ $progressPercent }}%</span>
                    </div>
                </div>
            </div>
            {{-- Motivational message --}}
            @if($motivationalMsg)
                <p class="mt-3 text-sm text-blue-100 italic">{{ $motivationalMsg }}</p>
            @endif
        </div>

        {{-- Lesson step indicator --}}
        <div class="px-6 py-3 bg-gray-50 border-t border-gray-100">
            <div class="flex items-center gap-1 overflow-x-auto">
                @foreach($lessons as $i => $l)
                    @php
                        $done = in_array($l->id, $completedLessonIds);
                        $isCurrent = $l->id === $lesson->id;
                    @endphp
                    <div class="flex items-center gap-1 flex-shrink-0">
                        @if($done)
                            <a href="{{ route('learn.courses.lesson', [$course, $l]) }}" title="{{ $l->title }}"
                               class="w-7 h-7 rounded-full flex items-center justify-center bg-green-500 text-white hover:bg-green-600 transition">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            </a>
                        @elseif($isCurrent)
                            <div class="w-7 h-7 rounded-full flex items-center justify-center bg-blue-600 text-white text-xs font-bold ring-2 ring-blue-300 ring-offset-1">
                                {{ $i + 1 }}
                            </div>
                        @else
                            <a href="{{ route('learn.courses.lesson', [$course, $l]) }}" title="{{ $l->title }}"
                               class="w-7 h-7 rounded-full flex items-center justify-center bg-gray-200 text-gray-500 text-xs font-medium hover:bg-gray-300 transition">
                                {{ $i + 1 }}
                            </a>
                        @endif
                        @if($i < $lessons->count() - 1)
                            <div class="w-4 h-0.5 {{ $done ? 'bg-green-400' : 'bg-gray-200' }} rounded-full"></div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Lesson content card --}}
    <div class="card mb-6">
        @if($lesson->content_type === 'scorm' && $lesson->scorm_package_path)
            {{-- SCORM Player --}}
            <div class="px-6 py-4 bg-blue-50 border-b border-blue-100">
                <div class="flex items-center gap-2 text-sm text-blue-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    SCORM {{ $lesson->scorm_version }} content — your progress is tracked automatically.
                </div>
            </div>
            <script>
                window.SCORM_CONFIG = {
                    lessonId: {{ $lesson->id }},
                    csrfToken: '{{ csrf_token() }}',
                    baseUrl: '{{ rtrim(config("app.url"), "/") }}'
                };
            </script>
            <script src="{{ asset('js/scorm-rte.js') }}"></script>
            <div style="height: 600px; background: #f9fafb;">
                <iframe id="scorm-frame"
                        src="{{ asset('storage/' . $lesson->scorm_package_path . '/' . $lesson->scorm_entry_point) }}"
                        class="w-full h-full border-0"
                        allow="fullscreen"
                        sandbox="allow-scripts allow-same-origin allow-forms allow-popups"></iframe>
            </div>
        @elseif($lesson->content_type === 'video' && $lesson->video_url)
            <div class="aspect-video bg-black rounded-t-xl overflow-hidden">
                <iframe src="{{ $lesson->video_url }}" class="w-full h-full" frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen></iframe>
            </div>
        @endif

        @if($lesson->content_type !== 'scorm')
            <div class="px-6 py-8 sm:px-10 sm:py-10 lesson-content">
                {!! $lesson->content !!}
            </div>
        @endif
    </div>

    {{-- Key takeaway box --}}
    @if($lesson->content_type !== 'scorm')
    <div class="card mb-6 border-l-4 border-l-amber-400 bg-amber-50">
        <div class="px-6 py-5">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0 w-8 h-8 bg-amber-100 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-amber-900 text-sm">Key Takeaway</h3>
                    <p class="text-sm text-amber-800 mt-1">Remember to apply what you've learned here in your day-to-day work. Cybersecurity is a shared responsibility — staying informed protects you and your organization.</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- What's next preview --}}
    @if($nextLesson && !$isCompleted)
    <div class="card mb-6">
        <div class="px-6 py-4">
            <p class="text-xs text-gray-400 uppercase font-semibold tracking-wider mb-2">Up next</p>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center text-blue-600 font-bold text-sm">
                    {{ $currentIndex !== false ? $currentIndex + 2 : '' }}
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">{{ $nextLesson->title }}</h3>
                    <p class="text-xs text-gray-400">{{ $nextLesson->duration_minutes }} min &middot; {{ ucfirst($nextLesson->content_type) }}</p>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

{{-- Sticky bottom navigation bar --}}
<div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-lg z-40">
    <div class="max-w-4xl mx-auto px-4 py-3 flex items-center justify-between">
        <div>
            @if($prevLesson)
                <a href="{{ route('learn.courses.lesson', [$course, $prevLesson]) }}"
                   class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    <span class="hidden sm:inline">{{ \Illuminate\Support\Str::limit($prevLesson->title, 30) }}</span>
                    <span class="sm:hidden">Previous</span>
                </a>
            @else
                <a href="{{ route('learn.courses.show', $course) }}"
                   class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Course Overview
                </a>
            @endif
        </div>

        <div class="flex items-center gap-3">
            @if(!$isCompleted && $lesson->content_type !== 'scorm')
                <form method="POST" action="{{ route('learn.courses.complete-lesson', [$course, $lesson]) }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-green-600 text-white text-sm font-semibold rounded-lg hover:bg-green-700 transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Complete & Continue
                    </button>
                </form>
            @elseif(!$isCompleted && $lesson->content_type === 'scorm')
                <span class="text-sm text-gray-400 italic">Tracked by SCORM</span>
            @else
                <span class="inline-flex items-center gap-1 text-green-600 text-sm font-medium">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    Done
                </span>
                @if($nextLesson)
                    <a href="{{ route('learn.courses.lesson', [$course, $nextLesson]) }}"
                       class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition shadow-sm">
                        Next Lesson
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                @else
                    <a href="{{ route('learn.courses.show', $course) }}"
                       class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition shadow-sm">
                        Finish Course
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </a>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection
