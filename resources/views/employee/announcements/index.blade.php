@extends('layouts.app')
@section('title', 'Announcements — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-3">
        <h1 class="text-2xl font-bold text-gray-900">Announcements</h1>
        @if($unreadCount > 0)
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-500 text-white">{{ $unreadCount }} unread</span>
        @endif
    </div>

    @php
        $pinned = $announcements->getCollection()->where('is_pinned', true);
        $others = $announcements->getCollection()->where('is_pinned', false);
        $borders = ['info' => 'border-l-blue-500', 'warning' => 'border-l-yellow-500', 'urgent' => 'border-l-red-500', 'success' => 'border-l-green-500'];
    @endphp

    @if($announcements->isEmpty())
        <div class="card p-10 text-center text-gray-400">
            <svg class="mx-auto w-10 h-10 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
            No announcements right now. Check back later.
        </div>
    @endif

    @foreach([['Pinned', $pinned, true], ['Latest', $others, false]] as [$heading, $group, $isPinned])
        @if($group->isNotEmpty())
            <section class="space-y-3">
                @if($isPinned || $pinned->isNotEmpty())
                    <h2 class="text-sm font-semibold uppercase tracking-wider text-gray-400 flex items-center gap-1.5">
                        @if($isPinned)
                            <svg class="w-4 h-4 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path d="M5 5a2 2 0 012-2h6a2 2 0 012 2v2a2 2 0 01-1 1.73V12l2 3v1H4v-1l2-3V8.73A2 2 0 015 7V5zm4 12h2v2a1 1 0 11-2 0v-2z"/></svg>
                        @endif
                        {{ $heading }}
                    </h2>
                @endif

                @foreach($group as $a)
                    @php $isRead = in_array($a->id, $readIds); @endphp
                    <a href="{{ route('learn.announcements.show', $a->id) }}"
                       class="card block p-5 border-l-4 {{ $borders[$a->type] ?? 'border-l-gray-300' }} hover:shadow-md transition-shadow {{ $isRead ? '' : 'ring-1 ring-primary/20' }}">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2 mb-1.5">
                                    @if($a->is_pinned)
                                        <svg class="w-4 h-4 text-amber-500" fill="currentColor" viewBox="0 0 20 20" title="Pinned"><path d="M5 5a2 2 0 012-2h6a2 2 0 012 2v2a2 2 0 01-1 1.73V12l2 3v1H4v-1l2-3V8.73A2 2 0 015 7V5zm4 12h2v2a1 1 0 11-2 0v-2z"/></svg>
                                    @endif
                                    @include('partials.announcement-type-badge', ['type' => $a->type])
                                    <span class="text-xs text-gray-400">{{ ($a->published_at ?? $a->created_at)->diffForHumans() }}</span>
                                </div>
                                <h3 class="text-base {{ $isRead ? 'font-medium text-gray-700' : 'font-semibold text-gray-900' }}">{{ $a->title }}</h3>
                                <p class="text-sm text-gray-500 mt-1">{{ \Illuminate\Support\Str::limit(strip_tags($a->content), 180) }}</p>
                            </div>
                            <div class="flex-shrink-0 pt-1">
                                @if($isRead)
                                    <span class="inline-flex items-center gap-1 text-xs text-gray-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Read
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-primary">
                                        <span class="w-2 h-2 rounded-full bg-primary"></span> New
                                    </span>
                                @endif
                            </div>
                        </div>
                    </a>
                @endforeach
            </section>
        @endif
    @endforeach

    @if($announcements->hasPages())
        <div>{{ $announcements->links() }}</div>
    @endif
</div>
@endsection
