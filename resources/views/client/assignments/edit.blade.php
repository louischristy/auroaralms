@extends('layouts.app')
@section('title', 'Edit Assignment — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Edit Assignment</h1>
        <p class="text-sm text-gray-500 mt-1">Update assignment for <strong>{{ $assignment->course->title }}</strong>.</p>
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

    <form method="POST" action="{{ route('manage.assignments.update', $assignment) }}">
        @csrf
        @method('PUT')
        <div class="card p-6 space-y-5">
            <div>
                <label class="label" for="title">Title</label>
                <input type="text" name="title" id="title" value="{{ old('title', $assignment->title) }}" class="input" required>
            </div>

            <div>
                <label class="label" for="description">Description</label>
                <textarea name="description" id="description" rows="3" class="input">{{ old('description', $assignment->description) }}</textarea>
            </div>

            <div>
                <label class="label" for="instructions">Instructions</label>
                <textarea name="instructions" id="instructions" rows="5" class="input">{{ old('instructions', $assignment->instructions) }}</textarea>
                <p class="text-xs text-gray-400 mt-1">Detailed instructions for students on how to complete this assignment.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="label" for="due_days_after_enrollment">Due Days After Enrollment</label>
                    <input type="number" name="due_days_after_enrollment" id="due_days_after_enrollment" value="{{ old('due_days_after_enrollment', $assignment->due_days_after_enrollment) }}" class="input" min="1" placeholder="Optional">
                </div>

                <div>
                    <label class="label" for="max_file_size_mb">Max File Size (MB)</label>
                    <input type="number" name="max_file_size_mb" id="max_file_size_mb" value="{{ old('max_file_size_mb', $assignment->max_file_size_mb) }}" class="input" min="1" max="100" required>
                </div>

                <div>
                    <label class="label" for="allowed_file_types">Allowed File Types</label>
                    <input type="text" name="allowed_file_types" id="allowed_file_types" value="{{ old('allowed_file_types', $assignment->allowed_file_types) }}" class="input" required>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2">
                    <input type="hidden" name="is_mandatory" value="0">
                    <input type="checkbox" name="is_mandatory" id="is_mandatory" value="1" {{ old('is_mandatory', $assignment->is_mandatory) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600">
                    <label for="is_mandatory" class="text-sm text-gray-700">Mandatory</label>
                </div>

                <div class="flex items-center gap-2">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $assignment->is_active) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600">
                    <label for="is_active" class="text-sm text-gray-700">Active</label>
                </div>
            </div>

            <div class="flex items-center gap-3 pt-3">
                <button type="submit" class="btn-primary">Update Assignment</button>
                <a href="{{ route('manage.assignments.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
            </div>
        </div>
    </form>
</div>
@endsection
