@extends('layouts.app')
@section('title', 'My Assignments — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">My Assignments</h1>
        <p class="text-sm text-gray-500 mt-1">View and submit assignments for your enrolled courses.</p>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">{{ session('error') }}</div>
    @endif

    <div class="card overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assignment</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Mandatory</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @forelse($assignments as $assignment)
                    @php
                        $submission = $assignment->submissions->first();
                    @endphp
                    <tr>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $assignment->course->title }}</td>
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
                        <td class="px-6 py-4 text-center">
                            @if($submission)
                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $submission->statusColor() }}">{{ $submission->statusLabel() }}</span>
                            @else
                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-500">Not Submitted</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right space-x-3">
                            @if($submission)
                                <a href="{{ route('learn.assignments.my-submission', $assignment) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">View Submission</a>
                            @endif
                            <a href="{{ route('learn.assignments.show', $assignment) }}" class="text-secondary hover:text-primary text-sm font-medium">
                                {{ $submission ? 'Details' : 'Submit' }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-400">No assignments available for your enrolled courses.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
