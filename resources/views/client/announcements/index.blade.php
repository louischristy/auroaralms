@extends('layouts.app')
@section('title', 'Announcements — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Announcements</h1>
            <p class="text-sm text-gray-500 mt-1">Broadcast news, alerts and reminders to your learners.</p>
        </div>
        <a href="{{ route('manage.announcements.create') }}" class="btn-primary">+ New Announcement</a>
    </div>

    {{-- Filters --}}
    <form method="GET" class="card p-4 flex flex-col sm:flex-row gap-3 items-end">
        <div class="flex-1 w-full">
            <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by title..." class="input w-full">
        </div>
        <div class="w-full sm:w-40">
            <label class="block text-xs font-medium text-gray-500 mb-1">Type</label>
            <select name="type" class="input w-full">
                <option value="">All Types</option>
                @foreach($types as $t)
                    <option value="{{ $t }}" {{ request('type') === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                @endforeach
            </select>
        </div>
        <div class="w-full sm:w-36">
            <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
            <select name="status" class="input w-full">
                <option value="">All</option>
                <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="btn-primary">Filter</button>
            <a href="{{ route('manage.announcements.index') }}" class="btn-secondary">Clear</a>
        </div>
    </form>

    <div class="card">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Title</th>
                        @if($showTenant)<th class="text-left px-4 py-3 font-medium text-gray-600">Tenant</th>@endif
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Type</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Audience</th>
                        <th class="text-center px-4 py-3 font-medium text-gray-600">Reads</th>
                        <th class="text-center px-4 py-3 font-medium text-gray-600">Status</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Date</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($announcements as $a)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    @if($a->is_pinned)
                                        <svg class="w-4 h-4 text-amber-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" title="Pinned"><path d="M5 5a2 2 0 012-2h6a2 2 0 012 2v2a2 2 0 01-1 1.73V12l2 3v1H4v-1l2-3V8.73A2 2 0 015 7V5zm4 12h2v2a1 1 0 11-2 0v-2z"/></svg>
                                    @endif
                                    <span class="font-medium text-gray-900">{{ $a->title }}</span>
                                </div>
                                @if($a->expires_at)
                                    <p class="text-xs mt-0.5 {{ $a->expires_at->isPast() ? 'text-red-500' : 'text-gray-400' }}">
                                        {{ $a->expires_at->isPast() ? 'Expired' : 'Expires' }} {{ $a->expires_at->format('M j, Y') }}
                                    </p>
                                @endif
                            </td>
                            @if($showTenant)
                                <td class="px-4 py-3 text-gray-600">{{ $a->tenant?->name ?? '—' }}</td>
                            @endif
                            <td class="px-4 py-3">@include('partials.announcement-type-badge', ['type' => $a->type])</td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ ucfirst($a->target_audience) }}
                                @if($a->department)<span class="block text-xs text-gray-400">{{ $a->department->name }}</span>@endif
                            </td>
                            <td class="px-4 py-3 text-center text-gray-600">{{ $a->reads_count }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($a->is_published)
                                    <span class="badge-success">Published</span>
                                @else
                                    <span class="badge bg-gray-100 text-gray-700">Draft</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-500 whitespace-nowrap">{{ ($a->published_at ?? $a->created_at)->format('M j, Y') }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <form method="POST" action="{{ route('manage.announcements.publish', $a->id) }}">
                                        @csrf
                                        <button type="submit" class="text-secondary hover:text-primary text-xs font-medium">{{ $a->is_published ? 'Unpublish' : 'Publish' }}</button>
                                    </form>
                                    <a href="{{ route('manage.announcements.edit', $a->id) }}" class="text-secondary hover:text-primary text-xs font-medium">Edit</a>
                                    <form method="POST" action="{{ route('manage.announcements.destroy', $a->id) }}"
                                          x-data @submit="if (!confirm('Delete this announcement?')) $event.preventDefault()">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700 text-xs font-medium">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ $showTenant ? 8 : 7 }}" class="px-4 py-8 text-center text-gray-400">No announcements found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($announcements->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">{{ $announcements->links() }}</div>
        @endif
    </div>
</div>
@endsection
