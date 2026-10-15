@extends('layouts.app')
@section('title', 'Career Paths — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Career Paths</h1>
        <p class="text-sm text-gray-500 mt-1">Browse available career progression paths and track your journey.</p>
    </div>

    @if($paths->isEmpty())
        <div class="card p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            <p class="mt-4 text-gray-500">No career paths are available yet.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($paths as $path)
                @php $enrolled = in_array($path->id, $enrolledIds); @endphp
                <div class="card p-6 flex flex-col">
                    <div class="flex items-start gap-3 mb-3">
                        @if($path->icon)
                            <span class="text-3xl">{{ $path->icon }}</span>
                        @else
                            <div class="w-12 h-12 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                            </div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <h3 class="font-semibold text-gray-900">{{ $path->title }}</h3>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $path->stages_count }} {{ Str::plural('stage', $path->stages_count) }}</p>
                        </div>
                    </div>

                    @if($path->description)
                        <p class="text-sm text-gray-500 mb-4 flex-1">{{ \Illuminate\Support\Str::limit($path->description, 120) }}</p>
                    @else
                        <div class="flex-1"></div>
                    @endif

                    <div class="mt-auto pt-3 border-t border-gray-100 flex items-center justify-between">
                        @if($enrolled)
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700">Enrolled</span>
                            <a href="{{ route('learn.career-paths.show', $path->id) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">View Progress</a>
                        @else
                            <span></span>
                            <div class="flex items-center gap-3">
                                <a href="{{ route('learn.career-paths.show', $path->id) }}" class="text-sm text-gray-500 hover:text-gray-700">Details</a>
                                <form method="POST" action="{{ route('learn.career-paths.enroll', $path->id) }}">
                                    @csrf
                                    <button type="submit" class="btn-primary text-sm">Enroll</button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
