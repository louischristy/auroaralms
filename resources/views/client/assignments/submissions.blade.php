@extends('layouts.app')
@section('title', 'Submissions — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Submissions: {{ $assignment->title }}</h1>
        <p class="text-sm text-gray-500 mt-1">Course: {{ $assignment->course->title }}</p>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">{{ session('success') }}</div>
    @endif

    <div class="card overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">File</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Grade</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submitted</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @forelse($submissions as $submission)
                    <tr>
                        <td class="px-6 py-4">
                            <p class="font-medium text-gray-900">{{ $submission->user->name }}</p>
                            <p class="text-xs text-gray-400">{{ $submission->user->email }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-gray-700">{{ $submission->file_name }}</p>
                            <p class="text-xs text-gray-400">{{ number_format($submission->file_size / 1024, 1) }} KB</p>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $submission->statusColor() }}">
                                {{ $submission->statusLabel() }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center text-sm text-gray-600">
                            {{ $submission->grade !== null ? $submission->grade . '/100' : '—' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ $submission->submitted_at->format('M j, Y g:ia') }}
                        </td>
                        <td class="px-6 py-4 text-right space-x-3">
                            <a href="{{ route('manage.submissions.review', $submission) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">Review</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-400">No submissions yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $submissions->links() }}

    <a href="{{ route('manage.assignments.index') }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; Back to Assignments</a>
</div>
@endsection
