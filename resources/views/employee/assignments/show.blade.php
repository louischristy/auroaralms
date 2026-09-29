@extends('layouts.app')
@section('title', $assignment->title . ' — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">{{ $assignment->title }}</h1>
        <p class="text-sm text-gray-500 mt-1">Course: {{ $assignment->course->title }}</p>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">{{ session('error') }}</div>
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

    {{-- Assignment Details --}}
    <div class="card p-6 space-y-4">
        @if($assignment->description)
            <div>
                <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-1">Description</h2>
                <p class="text-gray-700">{{ $assignment->description }}</p>
            </div>
        @endif

        @if($assignment->instructions)
            <div>
                <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-1">Instructions</h2>
                <div class="text-gray-700 whitespace-pre-line">{{ $assignment->instructions }}</div>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-3 border-t text-sm">
            <div>
                <span class="text-gray-500">Accepted formats:</span>
                <span class="font-medium text-gray-900 ml-1">{{ $assignment->allowed_file_types }}</span>
            </div>
            <div>
                <span class="text-gray-500">Max file size:</span>
                <span class="font-medium text-gray-900 ml-1">{{ $assignment->max_file_size_mb }} MB</span>
            </div>
            <div>
                @if($assignment->is_mandatory)
                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-700">Mandatory</span>
                @else
                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-500">Optional</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Submission Form --}}
    @php
        $canSubmit = !$submission || in_array($submission->status, ['rejected']);
    @endphp

    @if($canSubmit)
        <form method="POST" action="{{ route('learn.assignments.submit', $assignment) }}" enctype="multipart/form-data">
            @csrf
            <div class="card p-6 space-y-5">
                <h2 class="text-lg font-semibold text-gray-900">{{ $submission ? 'Resubmit Assignment' : 'Submit Assignment' }}</h2>

                <div>
                    <label class="label" for="file">Upload File</label>
                    <input type="file" name="file" id="file" class="input" required accept="{{ '.' . implode(',.', $assignment->allowedFileTypesArray()) }}">
                    <p class="text-xs text-gray-400 mt-1">Accepted: {{ $assignment->allowed_file_types }} (max {{ $assignment->max_file_size_mb }} MB)</p>
                </div>

                <div>
                    <label class="label" for="notes">Notes (optional)</label>
                    <textarea name="notes" id="notes" rows="3" class="input" placeholder="Any notes for the reviewer...">{{ old('notes') }}</textarea>
                </div>

                <button type="submit" class="btn-primary">Submit Assignment</button>
            </div>
        </form>
    @elseif($submission)
        <div class="card p-6">
            <p class="text-sm text-gray-600">
                You have already submitted this assignment. Current status:
                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium ml-1 {{ $submission->statusColor() }}">{{ $submission->statusLabel() }}</span>
            </p>
            <a href="{{ route('learn.assignments.my-submission', $assignment) }}" class="text-sm text-blue-600 hover:text-blue-800 mt-2 inline-block">View your submission</a>
        </div>
    @endif

    <a href="{{ route('learn.assignments.index') }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; Back to Assignments</a>
</div>
@endsection
