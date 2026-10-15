@extends('layouts.app')
@section('title', __('Career Paths') . ' — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="space-y-8">
    {{-- Hero header --}}
    <div class="bg-gradient-to-r from-indigo-600 via-blue-600 to-blue-700 rounded-2xl px-8 py-10 text-white">
        <h1 class="text-3xl font-bold">{{ __('Career Paths') }}</h1>
        <p class="text-blue-100 mt-2 text-sm max-w-xl">{{ __('Follow a structured progression path to advance your security skills and career.') }}</p>

        @php
            $enrolledCount = count($enrolledIds);
        @endphp
        <div class="flex items-center gap-6 mt-6">
            <div class="bg-white/10 backdrop-blur rounded-xl px-5 py-3">
                <p class="text-2xl font-bold">{{ $paths->count() }}</p>
                <p class="text-xs text-blue-200">{{ __('Available') }}</p>
            </div>
            <div class="bg-white/10 backdrop-blur rounded-xl px-5 py-3">
                <p class="text-2xl font-bold">{{ $enrolledCount }}</p>
                <p class="text-xs text-blue-200">{{ __('Enrolled') }}</p>
            </div>
        </div>
    </div>

    {{-- Career path cards --}}
    @if($paths->isEmpty())
        <div class="card p-12 text-center">
            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            <p class="text-gray-500">{{ __('No career paths are available yet. Check back soon!') }}</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($paths as $path)
                @php $enrolled = in_array($path->id, $enrolledIds); @endphp
                <a href="{{ route('learn.career-paths.show', $path->id) }}"
                   class="group card overflow-hidden hover:shadow-lg transition-all duration-300 hover:-translate-y-0.5 flex flex-col">
                    {{-- Color bar --}}
                    <div class="h-1.5 {{ $enrolled ? 'bg-emerald-500' : 'bg-indigo-500' }}"></div>

                    <div class="p-5 flex-1 flex flex-col">
                        <div class="flex items-start gap-3 mb-3">
                            @if($path->icon)
                                <span class="text-3xl">{{ $path->icon }}</span>
                            @else
                                <div class="w-12 h-12 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                                </div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <h3 class="font-semibold text-gray-900 group-hover:text-indigo-600 transition">{{ $path->title }}</h3>
                                <p class="text-xs text-gray-400 mt-0.5">{{ $path->stages_count }} {{ $path->stages_count === 1 ? __('stage') : __('stages') }}</p>
                            </div>
                            @if($enrolled)
                                <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-700">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                    {{ __('Enrolled') }}
                                </span>
                            @endif
                        </div>

                        @if($path->description)
                            <p class="text-sm text-gray-500 line-clamp-2 flex-1">{{ $path->description }}</p>
                        @else
                            <div class="flex-1"></div>
                        @endif

                        <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between">
                            @if($enrolled)
                                <span class="text-sm font-medium text-emerald-600">{{ __('View Progress') }} →</span>
                            @else
                                <span class="text-sm font-medium text-indigo-600">{{ __('Learn More') }} →</span>
                            @endif
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
@endsection
