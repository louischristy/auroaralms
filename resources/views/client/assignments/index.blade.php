@extends('layouts.app')
@section('title', 'Assignments — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Assignments</h1>
            <p class="text-sm text-gray-500 mt-1">Manage course assignments and review submissions.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">{{ session('success') }}</div>
    @endif

    @forelse($assignments as $courseId => $courseAssignments)
        @php $course = $courses[$courseId] ?? null; @endphp
        @if($course)
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">{{ $course->title }}</h2>
                <a href="{{ route('manage.assignments.create', $course) }}" class="btn-primary text-sm">+ Add Assignment</a>
            </div>

            <div class="card overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Mandatory</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Submissions</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @foreach($courseAssignments as $assignment)
                            <tr>
                                <td class="px-6 py-4">
                                    <p class="font-medium text-gray-900">{{ $assignment->title }}</p>
                                    <p class="text-xs text-gray-400">{{ Str::limit($assignment->description, 60) }}</p>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($assignment->is_mandatory)
                                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-700">Required</span>
                                    @else
                                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-500">Optional</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center text-sm text-gray-600">
                                    {{ $assignment->submissions->count() }}
                                    @if($assignment->submissions->where('status', 'submitted')->count() + $assignment->submissions->where('status', 'resubmitted')->count() > 0)
                                        <span class="text-yellow-600">({{ $assignment->submissions->where('status', 'submitted')->count() + $assignment->submissions->where('status', 'resubmitted')->count() }} pending)</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $assignment->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                        {{ $assignment->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right space-x-3">
                                    <a href="{{ route('manage.assignments.submissions', $assignment) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">Submissions</a>
                                    <a href="{{ route('manage.assignments.edit', $assignment) }}" class="text-secondary hover:text-primary text-sm font-medium">Edit</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    @empty
        <div class="card p-12 text-center text-gray-400">
            <p>No assignments yet. Add assignments from the course builder.</p>
        </div>
    @endforelse
</div>
@endsection
