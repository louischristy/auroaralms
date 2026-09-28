@extends('layouts.app')
@section('title', $announcement->title . ' — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="max-w-3xl space-y-6">
    <a href="{{ route('learn.announcements.index') }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; All announcements</a>

    <article class="card p-6 sm:p-8">
        <div class="flex flex-wrap items-center gap-2 mb-3">
            @if($announcement->is_pinned)
                <span class="inline-flex items-center gap-1 text-xs font-medium text-amber-600">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M5 5a2 2 0 012-2h6a2 2 0 012 2v2a2 2 0 01-1 1.73V12l2 3v1H4v-1l2-3V8.73A2 2 0 015 7V5zm4 12h2v2a1 1 0 11-2 0v-2z"/></svg> Pinned
                </span>
            @endif
            @include('partials.announcement-type-badge', ['type' => $announcement->type])
        </div>
        <h1 class="text-2xl font-bold text-gray-900">{{ $announcement->title }}</h1>
        <p class="text-sm text-gray-500 mt-1">
            {{ ($announcement->published_at ?? $announcement->created_at)->format('F j, Y') }}
            @if($announcement->creator) &middot; {{ $announcement->creator->name }} @endif
        </p>
        <div class="mt-6 text-gray-700 leading-relaxed whitespace-pre-line">{{ $announcement->content }}</div>
    </article>
</div>
@endsection
