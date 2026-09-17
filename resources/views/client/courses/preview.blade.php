@extends('layouts.app')
@section('title', 'Preview: ' . $course->title . ' — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <a href="{{ route('manage.courses.edit', $course) }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Editor
        </a>
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-100 text-amber-700 text-xs font-medium">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            Preview Mode
        </span>
    </div>

    {{-- Course hero --}}
    <div class="card overflow-hidden">
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-6 py-8 text-white">
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
                    {{ $course->lessons->count() }} {{ $course->lessons->count() === 1 ? 'lesson' : 'lessons' }}
                </span>
                <span class="inline-flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ $course->duration_minutes }} min
                </span>
                @if($course->quiz)
                    <span class="inline-flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        Quiz ({{ $course->passing_score }}% to pass)
                    </span>
                @endif
            </div>
        </div>
    </div>

    {{-- Objectives --}}
    @if($course->objectives)
        <div class="card p-6">
            <h2 class="font-semibold text-gray-900 mb-3">Learning Objectives</h2>
            <ul class="space-y-2">
                @foreach($course->objectives as $obj)
                    <li class="flex items-start gap-2 text-sm text-gray-600">
                        <svg class="w-4 h-4 text-green-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        {{ $obj }}
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Lessons --}}
    @if($course->lessons->isNotEmpty())
        <div class="card" x-data="{ openLesson: null }">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-900">Lessons</h2>
            </div>
            <div class="divide-y divide-gray-50">
                @foreach($course->lessons as $index => $lesson)
                    <div class="px-6 py-4">
                        <button @click="openLesson = openLesson === {{ $index }} ? null : {{ $index }}" class="w-full flex items-center justify-between text-left">
                            <div class="flex items-center gap-3">
                                <span class="flex items-center justify-center w-7 h-7 rounded-full bg-blue-100 text-blue-700 text-xs font-bold">{{ $index + 1 }}</span>
                                <div>
                                    <p class="font-medium text-gray-900">{{ $lesson->title }}</p>
                                    <p class="text-xs text-gray-400">{{ $lesson->duration_minutes }} min &middot; {{ ucfirst($lesson->content_type) }}</p>
                                </div>
                            </div>
                            <svg class="w-5 h-5 text-gray-400 transition-transform" :class="openLesson === {{ $index }} ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="openLesson === {{ $index }}" x-cloak x-transition class="mt-4 pl-10">
                            @if($lesson->video_url)
                                <div class="mb-3 p-3 bg-blue-50 rounded text-sm text-blue-700">
                                    Video: {{ $lesson->video_url }}
                                </div>
                            @endif
                            @if($lesson->content)
                                <div class="lesson-content text-sm text-gray-700 leading-relaxed">
                                    {!! $lesson->content !!}
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Quiz preview --}}
    @if($course->quiz && $course->quiz->questions->isNotEmpty())
        <div class="card">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-900">Quiz: {{ $course->quiz->title }}</h2>
                @if($course->quiz->instructions)
                    <p class="text-sm text-gray-500 mt-1">{{ $course->quiz->instructions }}</p>
                @endif
                <div class="flex gap-4 mt-2 text-xs text-gray-400">
                    <span>{{ $course->quiz->questions->count() }} questions</span>
                    @if($course->quiz->time_limit_minutes)
                        <span>{{ $course->quiz->time_limit_minutes }} min time limit</span>
                    @endif
                    <span>{{ $course->quiz->max_attempts ?: 'Unlimited' }} attempts</span>
                </div>
            </div>
            <div class="divide-y divide-gray-50">
                @foreach($course->quiz->questions as $qi => $question)
                    <div class="px-6 py-4">
                        <p class="font-medium text-gray-900 text-sm mb-2">{{ $qi + 1 }}. {{ $question->question }}</p>
                        <div class="ml-4 space-y-1">
                            @foreach($question->answers as $answer)
                                <div class="flex items-center gap-2 text-sm {{ $answer->is_correct ? 'text-green-700 font-medium' : 'text-gray-500' }}">
                                    @if($answer->is_correct)
                                        <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                    @else
                                        <span class="w-4 h-4 rounded-full border border-gray-300 inline-block"></span>
                                    @endif
                                    {{ $answer->answer_text }}
                                </div>
                            @endforeach
                        </div>
                        @if($question->explanation)
                            <p class="ml-4 mt-2 text-xs text-blue-600 bg-blue-50 p-2 rounded">{{ $question->explanation }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
