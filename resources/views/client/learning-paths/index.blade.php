@extends('layouts.app')
@section('title', 'Learning Paths — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Learning Paths</h1>
            <p class="text-sm text-gray-500 mt-1">Platform paths shared with you and paths your organization created.</p>
        </div>
        @if($tenantId)
            <a href="{{ route('manage.learning-paths.create') }}" class="btn-primary text-sm">+ New Learning Path</a>
        @endif
    </div>

    <div class="card p-4">
        <form method="GET" x-data class="flex items-center gap-4 flex-wrap">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by title..." class="input w-64">
            <select name="source" class="input w-44" x-on:change="$el.form.submit()">
                <option value="">All Sources</option>
                <option value="platform" {{ request('source') === 'platform' ? 'selected' : '' }}>Platform</option>
                <option value="own" {{ request('source') === 'own' ? 'selected' : '' }}>{{ $tenantId ? 'Our Paths' : 'Tenant Paths' }}</option>
            </select>
            <select name="difficulty" class="input w-44" x-on:change="$el.form.submit()">
                <option value="">All Difficulties</option>
                @foreach(['beginner', 'intermediate', 'advanced'] as $d)
                    <option value="{{ $d }}" {{ request('difficulty') === $d ? 'selected' : '' }}>{{ ucfirst($d) }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-primary text-sm">Filter</button>
            @if(request()->hasAny(['search', 'source', 'difficulty']))
                <a href="{{ route('manage.learning-paths.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Clear</a>
            @endif
        </form>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Learning Path</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Source</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Courses</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Learners</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Difficulty</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @forelse($paths as $path)
                    @php $own = $path->tenant_id !== null; @endphp
                    <tr>
                        <td class="px-6 py-4">
                            <p class="font-medium text-gray-900">{{ $path->title }}</p>
                            <p class="text-xs text-gray-400">{{ \Illuminate\Support\Str::limit($path->description, 60) }}</p>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            @if($own)
                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700">{{ $tenantId ? 'Ours' : ($path->tenant->name ?? 'Tenant') }}</span>
                            @else
                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-700">Platform</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center text-sm text-gray-600">{{ $path->courses_count }}</td>
                        <td class="px-6 py-4 text-center text-sm text-gray-600">{{ $path->enrollments_count }}</td>
                        <td class="px-6 py-4 text-sm">
                            @php $dc = ['beginner' => 'bg-green-100 text-green-700', 'intermediate' => 'bg-yellow-100 text-yellow-700', 'advanced' => 'bg-red-100 text-red-700'][$path->difficulty] ?? 'bg-gray-100 text-gray-600'; @endphp
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $dc }}">{{ ucfirst($path->difficulty) }}</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $path->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $path->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right whitespace-nowrap">
                            <a href="{{ route('manage.learning-paths.assign-users', $path->id) }}" class="text-secondary hover:text-primary text-sm font-medium">Assign</a>
                            @if($own && $tenantId)
                                <a href="{{ route('manage.learning-paths.edit', $path->id) }}" class="ml-3 text-secondary hover:text-primary text-sm font-medium">Edit</a>
                                <form method="POST" action="{{ route('manage.learning-paths.destroy', $path->id) }}" class="inline" x-data
                                      x-on:submit="if (!confirm('Delete this learning path?')) $event.preventDefault()">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="ml-3 text-red-600 hover:text-red-800 text-sm font-medium">Delete</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-6 py-12 text-center text-gray-400">No learning paths found.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    <div>{{ $paths->links() }}</div>
</div>
@endsection
