@extends('layouts.app')
@section('title', 'Add Assignment — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Add Assignment</h1>
        <p class="text-sm text-gray-500 mt-1">Create a new assignment for <strong>{{ $course->title }}</strong>.</p>
    </div>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
            <ul class="list-disc list-inside text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('manage.assignments.store', $course) }}">
        @csrf
        <div class="card p-6 space-y-5">
            <div>
                <label class="label" for="title">Title</label>
                <input type="text" name="title" id="title" value="{{ old('title') }}" class="input" required>
            </div>

            <div>
                <label class="label" for="description">Description</label>
                <textarea name="description" id="description" rows="3" class="input">{{ old('description') }}</textarea>
            </div>

            <div>
                <label class="label" for="instructions">Instructions</label>
                <textarea name="instructions" id="instructions" rows="5" class="input">{{ old('instructions') }}</textarea>
                <p class="text-xs text-gray-400 mt-1">Detailed instructions for students on how to complete this assignment.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="label" for="due_days_after_enrollment">Due Days After Enrollment</label>
                    <input type="number" name="due_days_after_enrollment" id="due_days_after_enrollment" value="{{ old('due_days_after_enrollment') }}" class="input" min="1" placeholder="Optional">
                    <p class="text-xs text-gray-400 mt-1">Auto-sets due date X days after enrollment.</p>
                </div>

                <div>
                    <label class="label" for="max_file_size_mb">Max File Size (MB)</label>
                    <input type="number" name="max_file_size_mb" id="max_file_size_mb" value="{{ old('max_file_size_mb', 10) }}" class="input" min="1" max="100" required>
                </div>

                <div>
                    <label class="label" for="allowed_file_types">Allowed File Types</label>
                    <input type="text" name="allowed_file_types" id="allowed_file_types" value="{{ old('allowed_file_types', 'pdf,doc,docx,ppt,pptx,xls,xlsx,zip') }}" class="input" required>
                    <p class="text-xs text-gray-400 mt-1">Comma-separated extensions.</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <input type="hidden" name="is_mandatory" value="0">
                <input type="checkbox" name="is_mandatory" id="is_mandatory" value="1" {{ old('is_mandatory') ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600">
                <label for="is_mandatory" class="text-sm text-gray-700">Mandatory — course cannot be completed without this assignment</label>
            </div>

            <div class="flex items-center gap-3 pt-3">
                <button type="submit" class="btn-primary">Create Assignment</button>
                <a href="{{ route('manage.assignments.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
            </div>
        </div>
    </form>
</div>
@endsection
