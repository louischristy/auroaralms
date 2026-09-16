@extends('layouts.app')
@section('title', 'Course Builder — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Course Builder</h1>
            <p class="text-sm text-gray-500 mt-1">Create and manage custom training courses for your organization.</p>
        </div>
        <a href="{{ route('manage.courses.create') }}" class="btn-primary text-sm">+ New Course</a>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 gap-4">
        <div class="card p-5">
            <p class="text-sm text-gray-500">Your Custom Courses</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $courses->total() }}</p>
        </div>
        <div class="card p-5">
            <p class="text-sm text-gray-500">Platform Courses Assigned</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $platformCourseCount }}</p>
        </div>
    </div>

    {{-- Course list --}}
    <div class="card overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Lessons</th>
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
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $course->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $course->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right space-x-3">
                            <a href="{{ route('manage.courses.edit', $course) }}" class="text-secondary hover:text-primary text-sm font-medium">Edit</a>
                            <form method="POST" action="{{ route('manage.courses.destroy', $course) }}" class="inline" onsubmit="return confirm('Delete this course?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700 text-sm font-medium">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-400">No custom courses yet. Click "New Course" to get started.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $courses->links() }}
</div>
@endsection
