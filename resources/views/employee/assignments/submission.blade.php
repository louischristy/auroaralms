@extends('layouts.app')
@section('title', 'My Submission — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">My Submission</h1>
        <p class="text-sm text-gray-500 mt-1">
            Assignment: {{ $assignment->title }} &mdash;
            Course: {{ $assignment->course->title }}
        </p>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">{{ session('success') }}</div>
    @endif

    {{-- Submission Info --}}
    <div class="card p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Submission Details</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div>
                <span class="text-gray-500">File:</span>
                <span class="font-medium text-gray-900 ml-2">{{ $submission->file_name }}</span>
            </div>
            <div>
                <span class="text-gray-500">Size:</span>
                <span class="font-medium text-gray-900 ml-2">{{ number_format($submission->file_size / 1024, 1) }} KB</span>
            </div>
            <div>
                <span class="text-gray-500">Submitted:</span>
                <span class="font-medium text-gray-900 ml-2">{{ $submission->submitted_at->format('M j, Y g:ia') }}</span>
            </div>
            <div>
                <span class="text-gray-500">Status:</span>
                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium ml-2 {{ $submission->statusColor() }}">{{ $submission->statusLabel() }}</span>
            </div>
            @if($submission->grade !== null)
                <div>
                    <span class="text-gray-500">Grade:</span>
                    <span class="font-medium text-gray-900 ml-2">{{ $submission->grade }}/100</span>
                </div>
            @endif
        </div>

        @if($submission->notes)
            <div class="mt-4 pt-4 border-t">
                <p class="text-sm text-gray-500 mb-1">Your Notes:</p>
                <p class="text-sm text-gray-700">{{ $submission->notes }}</p>
            </div>
        @endif
    </div>

    {{-- Reviewer Feedback --}}
    @if($submission->reviewed_at)
        <div class="card p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Reviewer Feedback</h2>
            <div class="text-sm space-y-2">
                <div>
                    <span class="text-gray-500">Reviewed by:</span>
                    <span class="font-medium text-gray-900 ml-2">{{ $submission->reviewer?->name ?? 'Unknown' }}</span>
                </div>
                <div>
                    <span class="text-gray-500">Reviewed at:</span>
                    <span class="font-medium text-gray-900 ml-2">{{ $submission->reviewed_at->format('M j, Y g:ia') }}</span>
                </div>
                @if($submission->reviewer_feedback)
                    <div class="mt-3 pt-3 border-t">
                        <p class="text-gray-500 mb-1">Feedback:</p>
                        <div class="text-gray-700 whitespace-pre-line">{{ $submission->reviewer_feedback }}</div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Resubmit if rejected --}}
    @if($submission->status === 'rejected')
        <div class="card p-4 bg-yellow-50 border-yellow-200">
            <p class="text-sm text-yellow-800">Your submission was rejected. You may resubmit.</p>
            <a href="{{ route('learn.assignments.show', $assignment) }}" class="text-sm text-yellow-700 font-medium hover:underline mt-1 inline-block">Resubmit Assignment</a>
        </div>
    @endif

    <a href="{{ route('learn.assignments.index') }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; Back to Assignments</a>
</div>
@endsection
