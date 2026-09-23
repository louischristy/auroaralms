@extends('layouts.app')
@section('title', 'Create Course — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <a href="{{ route('manage.courses.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to Course Builder
    </a>

    {{-- Guidelines Card --}}
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-5">
        <div class="flex gap-3">
            <svg class="w-6 h-6 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div>
                <h3 class="text-sm font-semibold text-blue-900 mb-2">Course Builder Guidelines</h3>
                <ul class="text-sm text-blue-800 space-y-1.5">
                    <li class="flex items-start gap-1.5">
                        <span class="text-blue-500 mt-0.5">•</span>
                        <span><strong>Title:</strong> Keep it clear and action-oriented (e.g. "Identifying Phishing Emails" rather than "Email Security Module 1").</span>
                    </li>
                    <li class="flex items-start gap-1.5">
                        <span class="text-blue-500 mt-0.5">•</span>
                        <span><strong>Description:</strong> Explain what employees will learn and why it matters. Keep it under 2–3 sentences.</span>
                    </li>
                    <li class="flex items-start gap-1.5">
                        <span class="text-blue-500 mt-0.5">•</span>
                        <span><strong>Objectives:</strong> Use measurable verbs — "Identify", "Recognize", "Report", "Apply". 3–5 objectives per course is ideal.</span>
                    </li>
                    <li class="flex items-start gap-1.5">
                        <span class="text-blue-500 mt-0.5">•</span>
                        <span><strong>Duration:</strong> Aim for 15–30 min for beginner, 30–45 min for intermediate, and 45–60 min for advanced courses.</span>
                    </li>
                    <li class="flex items-start gap-1.5">
                        <span class="text-blue-500 mt-0.5">•</span>
                        <span><strong>Passing Score:</strong> 70% is standard. Use 80% for mandatory compliance courses.</span>
                    </li>
                    <li class="flex items-start gap-1.5">
                        <span class="text-blue-500 mt-0.5">•</span>
                        <span><strong>Difficulty:</strong> Beginner = awareness, Intermediate = applied skills, Advanced = deep-dive technical.</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="card p-6">
        <h1 class="text-xl font-bold text-gray-900 mb-6">Create New Course</h1>

        <form method="POST" action="{{ route('manage.courses.store') }}" class="space-y-5">
            @csrf

            <div>
                <label for="title" class="label">Course Title</label>
                <input type="text" id="title" name="title" value="{{ old('title') }}" required class="input @error('title') border-red-500 @enderror" placeholder="e.g. Identifying Phishing Emails">
                <p class="text-xs text-gray-400 mt-1">Clear, action-oriented name that tells employees what they'll learn.</p>
                @error('title')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="description" class="label">Description</label>
                <textarea id="description" name="description" rows="3" class="input" placeholder="This course teaches employees how to identify and respond to phishing attacks...">{{ old('description') }}</textarea>
                <p class="text-xs text-gray-400 mt-1">Briefly explain what employees will learn and why it's important. 2–3 sentences.</p>
            </div>

            <div>
                <label for="objectives" class="label">Learning Objectives (one per line)</label>
                <textarea id="objectives" name="objectives" rows="4" class="input" placeholder="Identify common phishing indicators in emails&#10;Report suspicious messages to the security team&#10;Apply safe browsing practices&#10;Recognize social engineering tactics">{{ old('objectives') }}</textarea>
                <p class="text-xs text-gray-400 mt-1">Start each with a measurable verb: Identify, Recognize, Report, Apply, Demonstrate. Aim for 3–5.</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="category" class="label">Category</label>
                    <select id="category" name="category" required class="input">
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-400 mt-1">Choose the security domain this course covers.</p>
                </div>
                <div>
                    <label for="difficulty" class="label">Difficulty</label>
                    <select id="difficulty" name="difficulty" required class="input">
                        <option value="beginner" {{ old('difficulty') === 'beginner' ? 'selected' : '' }}>Beginner</option>
                        <option value="intermediate" {{ old('difficulty') === 'intermediate' ? 'selected' : '' }}>Intermediate</option>
                        <option value="advanced" {{ old('difficulty') === 'advanced' ? 'selected' : '' }}>Advanced</option>
                    </select>
                    <p class="text-xs text-gray-400 mt-1">Beginner = awareness, Intermediate = applied, Advanced = technical.</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="duration_minutes" class="label">Duration (minutes)</label>
                    <input type="number" id="duration_minutes" name="duration_minutes" value="{{ old('duration_minutes', 30) }}" min="1" required class="input">
                    <p class="text-xs text-gray-400 mt-1">Recommended: 15–30 (beginner), 30–45 (intermediate), 45–60 (advanced).</p>
                </div>
                <div>
                    <label for="passing_score" class="label">Quiz Passing Score (%)</label>
                    <input type="number" id="passing_score" name="passing_score" value="{{ old('passing_score', 70) }}" min="0" max="100" required class="input">
                    <p class="text-xs text-gray-400 mt-1">Standard: 70%. Use 80% for mandatory compliance courses.</p>
                </div>
            </div>

            <div>
                <label class="flex items-center gap-2 text-sm text-gray-600">
                    <input type="checkbox" name="is_mandatory" value="1" {{ old('is_mandatory') ? 'checked' : '' }}
                           class="rounded border-gray-300 text-primary focus:ring-primary">
                    Mandatory course (all employees must complete)
                </label>
                <p class="text-xs text-gray-400 mt-1 ml-6">Mandatory courses appear in employee dashboards with due dates and send reminder notifications.</p>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t">
                <a href="{{ route('manage.courses.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Cancel</a>
                <button type="submit" class="btn-primary text-sm">Create Course</button>
            </div>
        </form>
    </div>
</div>
@endsection
