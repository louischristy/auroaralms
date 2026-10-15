@extends('layouts.app')
@section('title', $path->title . ' — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="space-y-8">
    {{-- Hero header --}}
    <div class="bg-gradient-to-r from-indigo-600 via-blue-600 to-blue-700 rounded-2xl px-8 py-10 text-white">
        <a href="{{ route('learn.career-paths.index') }}" class="inline-flex items-center gap-1 text-blue-200 hover:text-white text-sm mb-4 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            {{ __('Back to Career Paths') }}
        </a>
        <div class="flex items-start justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-3xl font-bold flex items-center gap-3">
                    @if($path->icon) <span>{{ $path->icon }}</span> @endif
                    {{ $path->title }}
                </h1>
                @if($path->description)
                    <p class="text-blue-100 mt-2 text-sm max-w-xl">{{ $path->description }}</p>
                @endif
            </div>
            @if(!$enrollment)
                <form method="POST" action="{{ route('learn.career-paths.enroll', $path->id) }}">
                    @csrf
                    <button type="submit" class="bg-white text-indigo-700 hover:bg-blue-50 px-6 py-2.5 rounded-xl text-sm font-semibold transition shadow-sm">{{ __('Enroll Now') }}</button>
                </form>
            @endif
        </div>

        @if($enrollment)
            @php
                $totalStages = $path->stages->count();
                $completedStages = collect($stageProgress)->where('complete', true)->count();
                $pct = $totalStages > 0 ? round(($completedStages / $totalStages) * 100) : 0;
            @endphp
            <div class="mt-6 flex items-center gap-6">
                <div class="bg-white/10 backdrop-blur rounded-xl px-5 py-3">
                    <p class="text-2xl font-bold">{{ $completedStages }}/{{ $totalStages }}</p>
                    <p class="text-xs text-blue-200">{{ __('Stages Complete') }}</p>
                </div>
                <div class="bg-white/10 backdrop-blur rounded-xl px-5 py-3">
                    <p class="text-2xl font-bold">{{ $pct }}%</p>
                    <p class="text-xs text-blue-200">{{ __('Progress') }}</p>
                </div>
                <div class="bg-white/10 backdrop-blur rounded-xl px-5 py-3">
                    <p class="text-2xl font-bold">{{ $enrollment->enrolled_at?->diffForHumans(null, true) }}</p>
                    <p class="text-xs text-blue-200">{{ __('Since Enrolled') }}</p>
                </div>
            </div>
            <div class="mt-4 w-full bg-white/20 rounded-full h-2.5">
                <div class="bg-white h-2.5 rounded-full transition-all duration-500" style="width: {{ $pct }}%"></div>
            </div>
        @endif
    </div>

    {{-- Stages Timeline --}}
    <div class="relative">
        {{-- Vertical line --}}
        <div class="absolute left-6 top-0 bottom-0 w-0.5 bg-gray-200 hidden md:block"></div>

        <div class="space-y-5">
            @foreach($path->stages as $stage)
                @php
                    $sp = $stageProgress[$stage->id] ?? ['complete' => false, 'course_done' => false, 'cert_done' => false];
                    $isComplete = $sp['complete'];
                    $isCurrent = $enrollment && $enrollment->current_stage_id === $stage->id;
                    $reqCourses = $stage->required_courses ?? [];
                    $reqCerts = $stage->required_certifications ?? [];
                @endphp
                <div class="flex gap-4 md:gap-6 relative">
                    {{-- Stage number circle --}}
                    <div class="flex-shrink-0 w-12 h-12 rounded-full flex items-center justify-center text-sm font-bold z-10
                        {{ $isComplete ? 'bg-emerald-500 text-white shadow-md' : ($isCurrent ? 'bg-indigo-600 text-white shadow-md ring-4 ring-indigo-100' : 'bg-gray-100 text-gray-400 border-2 border-gray-200') }}">
                        @if($isComplete)
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        @else
                            {{ $stage->stage_order + 1 }}
                        @endif
                    </div>

                    {{-- Stage card --}}
                    <div class="flex-1 card p-5 hover:shadow-md transition {{ $isComplete ? 'border-l-4 border-l-emerald-500' : ($isCurrent ? 'border-l-4 border-l-indigo-500' : '') }}">
                        <div class="flex items-start justify-between mb-2">
                            <div>
                                <h3 class="font-semibold text-gray-900 text-lg">{{ $stage->title }}</h3>
                                @if($isCurrent)
                                    <span class="inline-flex items-center gap-1 text-xs font-medium text-indigo-600 mt-1">
                                        <span class="w-1.5 h-1.5 bg-indigo-500 rounded-full animate-pulse"></span>
                                        {{ __('Current Stage') }}
                                    </span>
                                @endif
                            </div>
                            @if($stage->estimated_duration_days)
                                <span class="text-xs text-gray-400 bg-gray-50 px-2.5 py-1 rounded-full">~{{ $stage->estimated_duration_days }} {{ __('days') }}</span>
                            @endif
                        </div>

                        @if($stage->description)
                            <p class="text-sm text-gray-500 mb-4">{{ $stage->description }}</p>
                        @endif

                        {{-- Requirements --}}
                        @if(!empty($reqCourses) || !empty($reqCerts))
                            <div class="space-y-3">
                                @if(!empty($reqCourses))
                                    <div>
                                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">{{ __('Required Courses') }}</p>
                                        <div class="space-y-1.5">
                                            @foreach($reqCourses as $cid)
                                                @php $done = in_array($cid, $completedCourseIds); @endphp
                                                <div class="flex items-center gap-2.5 text-sm">
                                                    @if($done)
                                                        <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center flex-shrink-0">
                                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                        </span>
                                                        <span class="text-gray-400 line-through">{{ $courses[$cid]->title ?? "Course #$cid" }}</span>
                                                    @else
                                                        <span class="w-5 h-5 rounded-full border-2 border-gray-200 flex-shrink-0"></span>
                                                        <a href="{{ route('learn.courses.show', $cid) }}" class="text-indigo-600 hover:text-indigo-800 hover:underline">{{ $courses[$cid]->title ?? "Course #$cid" }}</a>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @if(!empty($reqCerts))
                                    <div>
                                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">{{ __('Required Certifications') }}</p>
                                        <div class="space-y-1.5">
                                            @foreach($reqCerts as $cert)
                                                @php $done = in_array(strtolower(trim($cert)), $completedCertNames); @endphp
                                                <div class="flex items-center gap-2.5 text-sm">
                                                    @if($done)
                                                        <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center flex-shrink-0">
                                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                        </span>
                                                        <span class="text-gray-400 line-through">{{ $cert }}</span>
                                                    @else
                                                        <span class="w-5 h-5 rounded-full border-2 border-gray-200 flex-shrink-0"></span>
                                                        <span class="text-gray-700">{{ $cert }}</span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
