@extends('layouts.app')
@section('title', 'Review Submission — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Review Submission</h1>
        <p class="text-sm text-gray-500 mt-1">
            Assignment: {{ $submission->assignment->title }} &mdash;
            Course: {{ $submission->assignment->course->title }}
        </p>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
            <ul class="list-disc list-inside text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Student Info --}}
    <div class="card p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-3">Student</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div>
                <span class="text-gray-500">Name:</span>
                <span class="font-medium text-gray-900 ml-2">{{ $submission->user->name }}</span>
            </div>
            <div>
                <span class="text-gray-500">Email:</span>
                <span class="font-medium text-gray-900 ml-2">{{ $submission->user->email }}</span>
            </div>
            <div>
                <span class="text-gray-500">Submitted:</span>
                <span class="font-medium text-gray-900 ml-2">{{ $submission->submitted_at->format('M j, Y g:ia') }}</span>
            </div>
            <div>
                <span class="text-gray-500">Status:</span>
                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium ml-2 {{ $submission->statusColor() }}">{{ $submission->statusLabel() }}</span>
            </div>
        </div>
    </div>

    {{-- File --}}
    <div class="card p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-3">Submitted File</h2>
        <div class="flex items-center justify-between">
            <div>
                <p class="font-medium text-gray-900">{{ $submission->file_name }}</p>
                <p class="text-sm text-gray-400">{{ number_format($submission->file_size / 1024, 1) }} KB</p>
            </div>
            <a href="{{ route('manage.submissions.download', $submission) }}" class="btn-primary text-sm">Download File</a>
        </div>
        @if($submission->notes)
            <div class="mt-4 pt-4 border-t">
                <p class="text-sm text-gray-500 mb-1">Student Notes:</p>
                <p class="text-sm text-gray-700">{{ $submission->notes }}</p>
            </div>
        @endif
    </div>

    {{-- Previous Review --}}
    @if($submission->reviewed_at)
        <div class="card p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-3">Previous Review</h2>
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
                    <div>
                        <span class="text-gray-500">Feedback:</span>
                        <p class="text-gray-700 mt-1">{{ $submission->reviewer_feedback }}</p>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Review Form --}}
    @if($submission->isReviewable())
        <form method="POST" action="{{ route('manage.submissions.review.submit', $submission) }}">
            @csrf
            <div class="card p-6 space-y-5">
                <h2 class="text-lg font-semibold text-gray-900">Your Review</h2>

                <div>
                    <label class="label" for="grade">Grade (0-100)</label>
                    <input type="number" name="grade" id="grade" value="{{ old('grade') }}" class="input" min="0" max="100" placeholder="Optional">
                </div>

                <div>
                    <label class="label" for="reviewer_feedback">Feedback</label>
                    <textarea name="reviewer_feedback" id="reviewer_feedback" rows="4" class="input">{{ old('reviewer_feedback') }}</textarea>
                </div>

                <div class="flex items-center gap-3 pt-3">
                    <button type="submit" name="status" value="approved" class="btn-primary bg-green-600 hover:bg-green-700">Approve</button>
                    <button type="submit" name="status" value="rejected" class="btn-primary bg-red-600 hover:bg-red-700">Reject</button>
                    <a href="{{ route('manage.assignments.submissions', $submission->assignment) }}" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
                </div>
            </div>
        </form>
    @endif
</div>
@endsection
