@extends('layouts.app')
@section('title', 'Create Course — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <a href="{{ route('platform.courses.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to Catalog
    </a>

    <div class="card p-6">
        <h1 class="text-xl font-bold text-gray-900 mb-6">Create New Course</h1>

        <form method="POST" action="{{ route('platform.courses.store') }}" class="space-y-5">
            @csrf

            <div>
                <label for="title" class="label">Course Title</label>
                <input type="text" id="title" name="title" value="{{ old('title') }}" required class="input @error('title') border-red-500 @enderror">
                @error('title')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="description" class="label">Description</label>
                <textarea id="description" name="description" rows="3" class="input">{{ old('description') }}</textarea>
            </div>

            <div>
                <label for="objectives" class="label">Learning Objectives (one per line)</label>
                <textarea id="objectives" name="objectives" rows="4" class="input" placeholder="Identify phishing emails&#10;Report suspicious messages&#10;Protect sensitive data">{{ old('objectives') }}</textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="category" class="label">Category</label>
                    <select id="category" name="category" required class="input">
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="difficulty" class="label">Difficulty</label>
                    <select id="difficulty" name="difficulty" required class="input">
                        <option value="beginner" {{ old('difficulty') === 'beginner' ? 'selected' : '' }}>Beginner</option>
                        <option value="intermediate" {{ old('difficulty') === 'intermediate' ? 'selected' : '' }}>Intermediate</option>
                        <option value="advanced" {{ old('difficulty') === 'advanced' ? 'selected' : '' }}>Advanced</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="duration_minutes" class="label">Duration (minutes)</label>
                    <input type="number" id="duration_minutes" name="duration_minutes" value="{{ old('duration_minutes', 30) }}" min="1" required class="input">
                </div>
                <div>
                    <label for="passing_score" class="label">Quiz Passing Score (%)</label>
                    <input type="number" id="passing_score" name="passing_score" value="{{ old('passing_score', 70) }}" min="0" max="100" required class="input">
                </div>
            </div>

            <div>
                <label class="flex items-center gap-2 text-sm text-gray-600">
                    <input type="checkbox" name="is_mandatory" value="1" {{ old('is_mandatory') ? 'checked' : '' }}
                           class="rounded border-gray-300 text-primary focus:ring-primary">
                    Mandatory course (all assigned employees must complete)
                </label>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t">
                <a href="{{ route('platform.courses.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Cancel</a>
                <button type="submit" class="btn-primary text-sm">Create Course</button>
            </div>
        </form>
    </div>
</div>
@endsection
