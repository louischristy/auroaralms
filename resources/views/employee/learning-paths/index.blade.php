@extends('layouts.app')
@section('title', 'My Learning Paths — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">My Learning Paths</h1>
        <p class="text-sm text-gray-500 mt-1">Guided sequences of courses assigned to you.</p>
    </div>

    @if($enrollments->isEmpty())
        <div class="card p-12 text-center">
            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
            </svg>
            <p class="text-gray-400 text-lg">No learning paths assigned yet.</p>
            <p class="text-gray-400 text-sm mt-1">Your administrator will assign learning paths to you.</p>
        </div>
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($enrollments as $enrollment)
                @php
                    $path = $enrollment->learningPath;
                    $progress = (int) round($enrollment->progress_percent);
                    $overdue = $enrollment->isOverdue();
                    $badge = match(true) {
                        $enrollment->status === 'completed' => ['Completed', 'bg-green-100 text-green-700'],
                        $overdue => ['Overdue', 'bg-red-100 text-red-700'],
                        $enrollment->status === 'in_progress' => ['In Progress', 'bg-blue-100 text-blue-700'],
                        default => ['Not Started', 'bg-gray-100 text-gray-600'],
                    };
                    $bar = $enrollment->status === 'completed' ? 'bg-green-500' : '';
                @endphp
                <a href="{{ route('learn.learning-paths.show', $path->id) }}" class="card p-5 flex flex-col hover:shadow-md transition">
                    <div class="flex items-start justify-between gap-3">
                        <h3 class="font-semibold text-gray-900 leading-snug">{{ $path->title }}</h3>
                        <span class="shrink-0 inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $badge[1] }}">{{ $badge[0] }}</span>
                    </div>
                    <p class="text-sm text-gray-500 mt-2 line-clamp-2">{{ $path->description }}</p>

                    <div class="flex items-center gap-3 text-xs text-gray-500 mt-4">
                        <span class="inline-flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            {{ $path->courses_count }} {{ \Illuminate\Support\Str::plural('course', $path->courses_count) }}
                        </span>
                        <span>{{ ucfirst($path->difficulty) }}</span>
                        @if($path->is_mandatory)<span class="text-red-600 font-medium">Mandatory</span>@endif
                    </div>

                    <div class="mt-4">
                        <div class="flex justify-between text-xs text-gray-500 mb-1">
                            <span>Progress</span><span class="font-medium text-gray-700">{{ $progress }}%</span>
                        </div>
                        <div class="w-full h-2 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-2 rounded-full {{ $bar }}" style="width: {{ $progress }}%; {{ $bar ? '' : 'background: var(--color-primary)' }}"></div>
                        </div>
                    </div>

                    @if($enrollment->due_date)
                        <p class="text-xs mt-3 {{ $overdue ? 'text-red-600 font-medium' : 'text-gray-400' }}">Due {{ $enrollment->due_date->format('M j, Y') }}</p>
                    @endif
                </a>
            @endforeach
        </div>
    @endif
</div>
@endsection
