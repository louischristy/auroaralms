@extends('layouts.app')
@section('title', $path->title . ' — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                @if($path->icon) <span class="mr-2">{{ $path->icon }}</span> @endif
                {{ $path->title }}
            </h1>
            <p class="text-sm text-gray-500 mt-1">{{ $path->description }}</p>
        </div>
        <div class="flex items-center gap-3">
            @if(!$enrollment)
                <form method="POST" action="{{ route('learn.career-paths.enroll', $path->id) }}">
                    @csrf
                    <button type="submit" class="btn-primary text-sm">Enroll Now</button>
                </form>
            @else
                <span class="inline-flex px-3 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700">
                    Enrolled {{ $enrollment->enrolled_at?->diffForHumans() }}
                </span>
            @endif
            <a href="{{ route('learn.career-paths.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Back</a>
        </div>
    </div>

    {{-- Progress Overview --}}
    @if($enrollment)
        @php
            $totalStages = $path->stages->count();
            $completedStages = collect($stageProgress)->where('complete', true)->count();
            $pct = $totalStages > 0 ? round(($completedStages / $totalStages) * 100) : 0;
        @endphp
        <div class="card p-6">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-700">Overall Progress</span>
                <span class="text-sm font-bold text-indigo-600">{{ $completedStages }}/{{ $totalStages }} stages completed</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3">
                <div class="bg-indigo-600 h-3 rounded-full transition-all duration-500" style="width: {{ $pct }}%"></div>
            </div>
        </div>
    @endif

    {{-- Stages Timeline --}}
    <div class="space-y-4">
        @foreach($path->stages as $stage)
            @php
                $sp = $stageProgress[$stage->id] ?? ['complete' => false, 'course_done' => false, 'cert_done' => false];
                $isComplete = $sp['complete'];
                $isCurrent = $enrollment && $enrollment->current_stage_id === $stage->id;
            @endphp
            <div class="card p-5 border-l-4 {{ $isComplete ? 'border-l-green-500' : ($isCurrent ? 'border-l-indigo-500' : 'border-l-gray-200') }}">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold
                        {{ $isComplete ? 'bg-green-100 text-green-700' : ($isCurrent ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-400') }}">
                        @if($isComplete)
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        @else
                            {{ $stage->stage_order + 1 }}
                        @endif
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center justify-between">
                            <h3 class="font-semibold text-gray-900">{{ $stage->title }}</h3>
                            @if($stage->estimated_duration_days)
                                <span class="text-xs text-gray-400">~{{ $stage->estimated_duration_days }} days</span>
                            @endif
                        </div>
                        @if($stage->description)
                            <p class="text-sm text-gray-500 mt-1">{{ $stage->description }}</p>
                        @endif

                        {{-- Requirements --}}
                        <div class="mt-3 space-y-2">
                            @if(!empty($stage->required_courses))
                                <div>
                                    <span class="text-xs font-medium text-gray-500 uppercase">Required Courses</span>
                                    <div class="mt-1 space-y-1">
                                        @foreach($stage->required_courses as $cid)
                                            @php $done = $enrollment && in_array($cid, \App\Models\CourseEnrollment::withoutTenantScope()->where('user_id', auth()->id())->where('status', 'completed')->pluck('course_id')->all()); @endphp
                                            <div class="flex items-center gap-2 text-sm">
                                                @if($done)
                                                    <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                @else
                                                    <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="2"/></svg>
                                                @endif
                                                <span class="{{ $done ? 'text-gray-500 line-through' : 'text-gray-700' }}">
                                                    {{ $courses[$cid]->title ?? "Course #$cid" }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if(!empty($stage->required_certifications))
                                <div>
                                    <span class="text-xs font-medium text-gray-500 uppercase">Required Certifications</span>
                                    <div class="mt-1 space-y-1">
                                        @foreach($stage->required_certifications as $cert)
                                            <div class="flex items-center gap-2 text-sm">
                                                <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="2"/></svg>
                                                <span class="text-gray-700">{{ $cert }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
