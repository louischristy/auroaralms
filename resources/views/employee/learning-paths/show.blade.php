@extends('layouts.app')
@section('title', $path->title . ' — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
@php
    $overall = (int) round($enrollment->progress_percent);
    $done = collect($steps)->where('completed', true)->count();
@endphp
<div class="max-w-3xl mx-auto space-y-6">
    <div>
        <a href="{{ route('learn.learning-paths.index') }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; My Learning Paths</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-1">{{ $path->title }}</h1>
        @if($path->description)<p class="text-sm text-gray-500 mt-1">{{ $path->description }}</p>@endif
    </div>

    <div class="card p-5">
        <div class="flex items-center justify-between text-sm mb-2">
            <span class="text-gray-600">{{ $done }} of {{ count($steps) }} courses completed</span>
            <span class="font-semibold text-gray-900">{{ $overall }}%</span>
        </div>
        <div class="w-full h-2.5 bg-gray-100 rounded-full overflow-hidden">
            <div class="h-2.5 rounded-full {{ $overall >= 100 ? 'bg-green-500' : '' }}" style="width: {{ $overall }}%; {{ $overall >= 100 ? '' : 'background: var(--color-primary)' }}"></div>
        </div>
        <div class="flex flex-wrap gap-x-4 gap-y-1 mt-3 text-xs text-gray-500">
            <span>{{ ucfirst($path->difficulty) }}</span>
            <span>{{ $path->is_sequential ? 'Complete in order' : 'Complete in any order' }}</span>
            @if($path->is_mandatory)<span class="text-red-600 font-medium">Mandatory</span>@endif
            @if($enrollment->due_date)
                <span class="{{ $enrollment->isOverdue() ? 'text-red-600 font-medium' : '' }}">Due {{ $enrollment->due_date->format('M j, Y') }}</span>
            @endif
        </div>
    </div>

    {{-- Timeline --}}
    <ol class="relative">
        @foreach($steps as $i => $step)
            @php
                $course = $step['course'];
                $last = $loop->last;
            @endphp
            <li class="relative pl-14 {{ $last ? '' : 'pb-6' }}">
                @if(!$last)
                    <span class="absolute left-[19px] top-10 bottom-0 w-0.5 {{ $step['completed'] ? 'bg-green-400' : 'bg-gray-200' }}"></span>
                @endif

                {{-- Marker --}}
                <span class="absolute left-0 top-0 w-10 h-10 rounded-full flex items-center justify-center
                    {{ $step['completed'] ? 'bg-green-500 text-white' : ($step['locked'] ? 'bg-gray-100 text-gray-400' : ($step['current'] ? 'text-white ring-4 ring-blue-100' : 'bg-white border-2 border-gray-300 text-gray-500')) }}"
                    @if($step['current']) style="background: var(--color-primary)" @endif>
                    @if($step['completed'])
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                    @elseif($step['locked'])
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4"/></svg>
                    @else
                        <span class="text-sm font-semibold">{{ $i + 1 }}</span>
                    @endif
                </span>

                {{-- Card --}}
                <div class="card p-5 {{ $step['current'] ? 'border-2 border-blue-400 shadow-md' : '' }} {{ $step['locked'] ? 'opacity-70 bg-gray-50' : '' }}">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="font-semibold text-gray-900">{{ $course->title }}</h3>
                                @if($step['current'])<span class="text-[10px] font-semibold uppercase tracking-wide px-1.5 py-0.5 rounded bg-blue-100 text-blue-700">Up next</span>@endif
                                @if($step['completed'])<span class="text-[10px] font-semibold uppercase tracking-wide px-1.5 py-0.5 rounded bg-green-100 text-green-700">Completed</span>@endif
                                @unless($step['required'])<span class="text-[10px] font-semibold uppercase tracking-wide px-1.5 py-0.5 rounded bg-gray-100 text-gray-500">Optional</span>@endunless
                            </div>
                            @if($course->description)
                                <p class="text-sm text-gray-500 mt-1 line-clamp-2">{{ $course->description }}</p>
                            @endif
                            <p class="text-xs text-gray-400 mt-2">
                                {{ $course->category }}@if($course->duration_minutes) &middot; {{ $course->duration_minutes }} min @endif
                            </p>
                        </div>

                        <div class="shrink-0">
                            @if($step['locked'])
                                <span class="inline-flex items-center gap-1 text-xs text-gray-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4"/></svg>
                                    Locked
                                </span>
                            @elseif($step['completed'])
                                <a href="{{ route('learn.courses.show', $course->id) }}" class="btn-outline text-sm">Review</a>
                            @else
                                <a href="{{ route('learn.courses.show', $course->id) }}" class="btn-primary text-sm">{{ $step['started'] ? 'Continue' : 'Start' }}</a>
                            @endif
                        </div>
                    </div>

                    @if($step['locked'])
                        <p class="text-xs text-gray-500 mt-3">{{ $step['lock_reason'] }}</p>
                    @else
                        <div class="mt-4">
                            <div class="flex justify-between text-xs text-gray-500 mb-1"><span>Progress</span><span>{{ $step['progress'] }}%</span></div>
                            <div class="w-full h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-1.5 rounded-full {{ $step['completed'] ? 'bg-green-500' : '' }}" style="width: {{ $step['progress'] }}%; {{ $step['completed'] ? '' : 'background: var(--color-primary)' }}"></div>
                            </div>
                        </div>
                    @endif
                </div>
            </li>
        @endforeach
    </ol>
</div>
@endsection
