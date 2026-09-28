@extends('layouts.app')
@section('title', 'Surveys — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Surveys</h1>
            <p class="text-sm text-gray-500 mt-1">Collect feedback on training and measure sentiment.</p>
        </div>
        <a href="{{ route('manage.surveys.create') }}" class="btn-primary">+ New Survey</a>
    </div>

    <form method="GET" class="card p-4 flex flex-col sm:flex-row gap-3 items-end">
        <div class="flex-1 w-full">
            <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by title..." class="input w-full">
        </div>
        <div class="w-full sm:w-44">
            <label class="block text-xs font-medium text-gray-500 mb-1">Type</label>
            <select name="type" class="input w-full">
                <option value="">All Types</option>
                @foreach($types as $t)
                    <option value="{{ $t }}" {{ request('type') === $t ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $t)) }}</option>
                @endforeach
            </select>
        </div>
        <div class="w-full sm:w-36">
            <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
            <select name="status" class="input w-full">
                <option value="">All</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="btn-primary">Filter</button>
            <a href="{{ route('manage.surveys.index') }}" class="btn-secondary">Clear</a>
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
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Course</th>
                        <th class="text-center px-4 py-3 font-medium text-gray-600">Questions</th>
                        <th class="text-center px-4 py-3 font-medium text-gray-600">Responses</th>
                        <th class="text-center px-4 py-3 font-medium text-gray-600">Status</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($surveys as $survey)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <span class="font-medium text-gray-900">{{ $survey->title }}</span>
                                <div class="flex gap-1.5 mt-1">
                                    @if($survey->is_required)<span class="badge bg-orange-100 text-orange-800">Required</span>@endif
                                    @if($survey->is_anonymous)<span class="badge bg-gray-100 text-gray-600">Anonymous</span>@endif
                                </div>
                            </td>
                            @if($showTenant)<td class="px-4 py-3 text-gray-600">{{ $survey->tenant?->name ?? '—' }}</td>@endif
                            <td class="px-4 py-3"><span class="badge-info">{{ ucwords(str_replace('_', ' ', $survey->type)) }}</span></td>
                            <td class="px-4 py-3 text-gray-600">{{ $survey->course?->title ?? '—' }}</td>
                            <td class="px-4 py-3 text-center text-gray-600">{{ $survey->questions_count }}</td>
                            <td class="px-4 py-3 text-center font-medium text-gray-900">{{ $survey->responses_count }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($survey->is_active)<span class="badge-success">Active</span>@else<span class="badge bg-gray-100 text-gray-700">Inactive</span>@endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('manage.surveys.results', $survey->id) }}" class="text-secondary hover:text-primary text-xs font-medium">Results</a>
                                    <a href="{{ route('manage.surveys.edit', $survey->id) }}" class="text-secondary hover:text-primary text-xs font-medium">Edit</a>
                                    <form method="POST" action="{{ route('manage.surveys.destroy', $survey->id) }}"
                                          x-data @submit="if (!confirm('Delete this survey? Its responses will no longer be accessible.')) $event.preventDefault()">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700 text-xs font-medium">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ $showTenant ? 8 : 7 }}" class="px-4 py-8 text-center text-gray-400">No surveys found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($surveys->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">{{ $surveys->links() }}</div>
        @endif
    </div>
</div>
@endsection
