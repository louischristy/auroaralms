@extends('layouts.app')
@section('title', 'Course Catalog — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Course Catalog</h1>
            <p class="text-sm text-gray-500 mt-1">Manage cybersecurity training courses.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('platform.courses.bulk-assign') }}" class="btn-outline text-sm">Bulk Assign to Tenants</a>
            <a href="{{ route('platform.courses.create') }}" class="btn-primary text-sm">+ New Course</a>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card p-4">
        <form method="GET" class="flex items-center gap-4 flex-wrap">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search courses..."
                   class="input w-64">
            <select name="category" class="input w-56" onchange="this.form.submit()">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-primary text-sm">Filter</button>
            @if(request()->hasAny(['search', 'category']))
                <a href="{{ route('platform.courses.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Clear</a>
            @endif
        </form>
    </div>

    {{-- Course list --}}
    <div class="card overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Lessons</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Enrollments</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @forelse($courses as $course)
                    <tr>
                        <td class="px-6 py-4">
                            <div>
                                <p class="font-medium text-gray-900">{{ $course->title }}</p>
                                <p class="text-xs text-gray-400">{{ Str::limit($course->description, 60) }}</p>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $course->category }}</td>
                        <td class="px-6 py-4 text-center text-sm text-gray-600">{{ $course->lessons_count }}</td>
                        <td class="px-6 py-4 text-center text-sm text-gray-600">{{ $course->enrollments_count }}</td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $course->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $course->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('platform.courses.edit', $course) }}" class="text-secondary hover:text-primary text-sm font-medium">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-400">No courses found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $courses->links() }}
</div>
@endsection
