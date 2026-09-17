@extends('layouts.app')
@section('title', 'Edit: ' . $course->title . ' — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="{ tab: '{{ session('_tab', 'details') }}' }">
    <a href="{{ route('platform.courses.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to Catalog
    </a>

    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">{{ $course->title }}</h1>
        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $course->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
            {{ $course->is_active ? 'Active' : 'Inactive' }}
        </span>
    </div>

    {{-- Tabs --}}
    <div class="border-b border-gray-200">
        <nav class="flex gap-6">
            @foreach(['details' => 'Details', 'lessons' => 'Lessons', 'quiz' => 'Quiz', 'tenants' => 'Assign Tenants'] as $key => $label)
                <button @click="tab = '{{ $key }}'"
                        :class="tab === '{{ $key }}' ? 'border-b-2 text-gray-900 font-medium' : 'text-gray-500 hover:text-gray-700'"
                        class="pb-3 text-sm transition-colors" style="border-color: var(--color-primary)">
                    {{ $label }}
                </button>
            @endforeach
        </nav>
    </div>

    {{-- Details tab --}}
    <div x-show="tab === 'details'" x-cloak>
        <div class="card p-6">
            <form method="POST" action="{{ route('platform.courses.update', $course) }}" class="space-y-5">
                @csrf @method('PUT')

                <div>
                    <label for="title" class="label">Course Title</label>
                    <input type="text" id="title" name="title" value="{{ old('title', $course->title) }}" required class="input">
                </div>

                <div>
                    <label for="description" class="label">Description</label>
                    <textarea id="description" name="description" rows="3" class="input">{{ old('description', $course->description) }}</textarea>
                </div>

                <div>
                    <label for="objectives" class="label">Learning Objectives (one per line)</label>
                    <textarea id="objectives" name="objectives" rows="4" class="input">{{ old('objectives', $course->objectives ? implode("\n", $course->objectives) : '') }}</textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="category" class="label">Category</label>
                        <select id="category" name="category" required class="input">
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}" {{ $course->category === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="difficulty" class="label">Difficulty</label>
                        <select id="difficulty" name="difficulty" required class="input">
                            <option value="beginner" {{ $course->difficulty === 'beginner' ? 'selected' : '' }}>Beginner</option>
                            <option value="intermediate" {{ $course->difficulty === 'intermediate' ? 'selected' : '' }}>Intermediate</option>
                            <option value="advanced" {{ $course->difficulty === 'advanced' ? 'selected' : '' }}>Advanced</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="duration_minutes" class="label">Duration (minutes)</label>
                        <input type="number" id="duration_minutes" name="duration_minutes" value="{{ $course->duration_minutes }}" min="1" required class="input">
                    </div>
                    <div>
                        <label for="passing_score" class="label">Quiz Passing Score (%)</label>
                        <input type="number" id="passing_score" name="passing_score" value="{{ $course->passing_score }}" min="0" max="100" required class="input">
                    </div>
                </div>

                <div class="flex items-center gap-6">
                    <label class="flex items-center gap-2 text-sm text-gray-600">
                        <input type="checkbox" name="is_active" value="1" {{ $course->is_active ? 'checked' : '' }}
                               class="rounded border-gray-300 text-primary focus:ring-primary">
                        Active
                    </label>
                    <label class="flex items-center gap-2 text-sm text-gray-600">
                        <input type="checkbox" name="is_mandatory" value="1" {{ $course->is_mandatory ? 'checked' : '' }}
                               class="rounded border-gray-300 text-primary focus:ring-primary">
                        Mandatory
                    </label>
                </div>

                <div class="flex justify-between pt-4 border-t">
                    <form method="POST" action="{{ route('platform.courses.destroy', $course) }}" onsubmit="return confirm('Delete this course? This cannot be undone.')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-sm text-red-600 hover:text-red-800">Delete Course</button>
                    </form>
                    <button type="submit" class="btn-primary text-sm">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Lessons tab --}}
    <div x-show="tab === 'lessons'" x-cloak>
        {{-- Existing lessons --}}
        @if($course->lessons->isNotEmpty())
            <div class="card mb-6">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="font-semibold text-gray-900">Lessons ({{ $course->lessons->count() }})</h2>
                </div>
                <div class="divide-y divide-gray-50">
                    @foreach($course->lessons as $lesson)
                        <div class="px-6 py-4" x-data="{ editing: false }">
                            <div x-show="!editing" class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <span class="text-xs text-gray-400 w-6 text-center">{{ $lesson->sort_order + 1 }}</span>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $lesson->title }}</p>
                                        <p class="text-xs text-gray-400">{{ $lesson->duration_minutes }} min &middot; {{ ucfirst($lesson->content_type) }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <button @click="editing = true" class="text-sm text-secondary hover:text-primary">Edit</button>
                                    <form method="POST" action="{{ route('platform.courses.lessons.destroy', [$course, $lesson]) }}"
                                          onsubmit="return confirm('Delete this lesson?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-sm text-red-500 hover:text-red-700">Delete</button>
                                    </form>
                                </div>
                            </div>

                            {{-- Edit form --}}
                            <div x-show="editing" x-cloak>
                                <form method="POST" action="{{ route('platform.courses.lessons.update', [$course, $lesson]) }}" class="space-y-4"
                                      enctype="multipart/form-data" x-data="{ editType: '{{ $lesson->content_type }}' }">
                                    @csrf @method('PUT')
                                    <input type="text" name="title" value="{{ $lesson->title }}" required class="input" placeholder="Lesson title">
                                    <div class="grid grid-cols-2 gap-4">
                                        <select name="content_type" class="input" x-model="editType">
                                            <option value="text" {{ $lesson->content_type === 'text' ? 'selected' : '' }}>Text</option>
                                            <option value="video" {{ $lesson->content_type === 'video' ? 'selected' : '' }}>Video</option>
                                            <option value="interactive" {{ $lesson->content_type === 'interactive' ? 'selected' : '' }}>Interactive</option>
                                            <option value="scorm" {{ $lesson->content_type === 'scorm' ? 'selected' : '' }}>SCORM Package</option>
                                        </select>
                                        <input type="number" name="duration_minutes" value="{{ $lesson->duration_minutes }}" min="1" class="input" placeholder="Minutes">
                                    </div>
                                    <div x-show="editType !== 'scorm'">
                                        <input type="url" name="video_url" value="{{ $lesson->video_url }}" class="input" placeholder="Video URL (optional)">
                                    </div>
                                    <div x-show="editType === 'scorm'" x-cloak class="space-y-2">
                                        @if($lesson->scorm_package_path)
                                            <p class="text-xs text-green-600">✓ SCORM {{ $lesson->scorm_version }} package uploaded</p>
                                        @endif
                                        <input type="file" name="scorm_package" accept=".zip" class="input">
                                        <p class="text-xs text-gray-400">Upload a new SCORM package to replace the existing one.</p>
                                    </div>
                                    <div x-show="editType !== 'scorm'">
                                        <textarea name="content" rows="8" class="input font-mono text-sm"
                                                  :required="editType !== 'scorm'">{{ $lesson->content }}</textarea>
                                    </div>
                                    <div class="flex justify-end gap-3">
                                        <button @click="editing = false" type="button" class="text-sm text-gray-500">Cancel</button>
                                        <button type="submit" class="btn-primary text-sm">Update Lesson</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Add lesson form --}}
        <div class="card p-6">
            <h2 class="font-semibold text-gray-900 mb-4">Add New Lesson</h2>
            <form method="POST" action="{{ route('platform.courses.lessons.store', $course) }}" class="space-y-4" enctype="multipart/form-data"
                  x-data="{ newType: 'text' }">
                @csrf
                <input type="text" name="title" required class="input" placeholder="Lesson title">
                <div class="grid grid-cols-2 gap-4">
                    <select name="content_type" class="input" x-model="newType">
                        <option value="text">Text</option>
                        <option value="video">Video</option>
                        <option value="interactive">Interactive</option>
                        <option value="scorm">SCORM Package</option>
                    </select>
                    <input type="number" name="duration_minutes" value="5" min="1" class="input" placeholder="Duration (min)">
                </div>
                <div x-show="newType !== 'scorm'">
                    <input type="url" name="video_url" class="input" placeholder="Video URL (YouTube/Vimeo embed, optional)">
                </div>
                <div x-show="newType === 'scorm'" x-cloak class="space-y-2">
                    <label class="label">SCORM Package (.zip)</label>
                    <input type="file" name="scorm_package" accept=".zip" class="input">
                    <p class="text-xs text-gray-400">Upload a SCORM 1.2 or 2004 package (ZIP). Max 100 MB.</p>
                </div>
                <div x-show="newType !== 'scorm'">
                    <textarea name="content" rows="10" class="input font-mono text-sm" placeholder="Lesson content (HTML supported)"
                              :required="newType !== 'scorm'"></textarea>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="btn-primary text-sm">Add Lesson</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Quiz tab --}}
    <div x-show="tab === 'quiz'" x-cloak>
        {{-- Quiz settings --}}
        <div class="card p-6 mb-6">
            <h2 class="font-semibold text-gray-900 mb-4">Quiz Settings</h2>
            <form method="POST" action="{{ route('platform.courses.quiz.store', $course) }}" class="space-y-4">
                @csrf
                <input type="text" name="title" value="{{ $course->quiz?->title ?? $course->title . ' Quiz' }}" required class="input" placeholder="Quiz title">
                <textarea name="instructions" rows="2" class="input" placeholder="Instructions for the quiz taker (optional)">{{ $course->quiz?->instructions }}</textarea>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="label">Time Limit (min)</label>
                        <input type="number" name="time_limit_minutes" value="{{ $course->quiz?->time_limit_minutes }}" min="1" class="input" placeholder="No limit">
                    </div>
                    <div>
                        <label class="label">Max Attempts</label>
                        <input type="number" name="max_attempts" value="{{ $course->quiz?->max_attempts ?? 3 }}" min="0" class="input">
                        <p class="text-xs text-gray-400 mt-1">0 = unlimited</p>
                    </div>
                    <div class="space-y-2 pt-6">
                        <label class="flex items-center gap-2 text-sm text-gray-600">
                            <input type="checkbox" name="shuffle_questions" value="1" {{ ($course->quiz?->shuffle_questions ?? true) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-primary focus:ring-primary">
                            Shuffle questions
                        </label>
                        <label class="flex items-center gap-2 text-sm text-gray-600">
                            <input type="checkbox" name="show_correct_answers" value="1" {{ ($course->quiz?->show_correct_answers ?? true) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-primary focus:ring-primary">
                            Show correct answers
                        </label>
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="btn-primary text-sm">Save Quiz Settings</button>
                </div>
            </form>
        </div>

        {{-- Questions --}}
        @if($course->quiz)
            @if($course->quiz->questions->isNotEmpty())
                <div class="card mb-6">
                    <div class="px-6 py-4 border-b border-gray-100">
                        <h2 class="font-semibold text-gray-900">Questions ({{ $course->quiz->questions->count() }})</h2>
                    </div>
                    <div class="divide-y divide-gray-50">
                        @foreach($course->quiz->questions as $qIndex => $question)
                            <div class="px-6 py-4">
                                <div class="flex items-start justify-between mb-2">
                                    <div class="flex items-start gap-3">
                                        <span class="text-xs text-gray-400 mt-1">Q{{ $qIndex + 1 }}</span>
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $question->question }}</p>
                                            <p class="text-xs text-gray-400">{{ ucfirst(str_replace('_', ' ', $question->question_type)) }} &middot; {{ $question->points }} pt{{ $question->points > 1 ? 's' : '' }}</p>
                                        </div>
                                    </div>
                                    <form method="POST" action="{{ route('platform.courses.questions.destroy', [$course, $question]) }}"
                                          onsubmit="return confirm('Delete this question?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-sm text-red-500 hover:text-red-700">Delete</button>
                                    </form>
                                </div>
                                <div class="ml-8 space-y-1">
                                    @foreach($question->answers as $answer)
                                        <div class="flex items-center gap-2 text-sm {{ $answer->is_correct ? 'text-green-700 font-medium' : 'text-gray-500' }}">
                                            @if($answer->is_correct)
                                                <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                            @else
                                                <span class="w-4 h-4 rounded-full border border-gray-300 inline-block"></span>
                                            @endif
                                            {{ $answer->answer_text }}
                                        </div>
                                    @endforeach
                                </div>
                                @if($question->explanation)
                                    <p class="ml-8 mt-2 text-xs text-blue-600 bg-blue-50 p-2 rounded">{{ $question->explanation }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Add question --}}
            <div class="card p-6" x-data="questionForm()">
                <h2 class="font-semibold text-gray-900 mb-4">Add Question</h2>
                <form method="POST" action="{{ route('platform.courses.questions.store', $course) }}" class="space-y-4">
                    @csrf
                    <textarea name="question" rows="2" required class="input" placeholder="Enter your question"></textarea>
                    <div class="grid grid-cols-2 gap-4">
                        <select name="question_type" class="input" x-model="type">
                            <option value="multiple_choice">Multiple Choice</option>
                            <option value="true_false">True / False</option>
                            <option value="multi_select">Multi-Select</option>
                        </select>
                        <input type="number" name="points" value="1" min="1" class="input" placeholder="Points">
                    </div>
                    <textarea name="explanation" rows="2" class="input" placeholder="Explanation shown after answer (optional)"></textarea>

                    <div>
                        <label class="label">Answers</label>
                        <template x-for="(answer, index) in answers" :key="index">
                            <div class="flex items-center gap-3 mb-2">
                                <input type="text" :name="'answers[' + index + '][text]'" x-model="answer.text" required class="input flex-1" placeholder="Answer text">
                                <label class="flex items-center gap-1 text-sm text-gray-600 flex-shrink-0">
                                    <input type="checkbox" :name="'answers[' + index + '][is_correct]'" value="1" x-model="answer.is_correct"
                                           class="rounded border-gray-300 text-primary focus:ring-primary">
                                    Correct
                                </label>
                                <button type="button" @click="removeAnswer(index)" x-show="answers.length > 2" class="text-red-400 hover:text-red-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </template>
                        <button type="button" @click="addAnswer()" class="text-sm text-secondary hover:text-primary mt-1">+ Add Answer</button>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="btn-primary text-sm">Add Question</button>
                    </div>
                </form>
            </div>
        @else
            <div class="card p-6 text-center text-gray-400">
                <p>Save quiz settings above first, then add questions.</p>
            </div>
        @endif
    </div>

    {{-- Tenants tab --}}
    <div x-show="tab === 'tenants'" x-cloak>
        <div class="card p-6">
            <h2 class="font-semibold text-gray-900 mb-4">Assign to Tenants</h2>
            <p class="text-sm text-gray-500 mb-4">Select which tenants have access to this course. All employees of selected tenants will see this course.</p>
            <form method="POST" action="{{ route('platform.courses.assign-tenants', $course) }}" class="space-y-4">
                @csrf
                @forelse($tenants as $tenant)
                    <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:bg-gray-50 cursor-pointer">
                        <input type="checkbox" name="tenant_ids[]" value="{{ $tenant->id }}"
                               {{ in_array($tenant->id, $assignedTenantIds) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-primary focus:ring-primary">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $tenant->name }}</p>
                            <p class="text-xs text-gray-400">{{ $tenant->slug }}</p>
                        </div>
                    </label>
                @empty
                    <p class="text-gray-400 text-sm">No tenants created yet.</p>
                @endforelse
                @if($tenants->isNotEmpty())
                    <div class="flex justify-end pt-4">
                        <button type="submit" class="btn-primary text-sm">Update Assignments</button>
                    </div>
                @endif
            </form>
        </div>
    </div>
</div>

<script>
function questionForm() {
    return {
        type: 'multiple_choice',
        answers: [
            { text: '', is_correct: false },
            { text: '', is_correct: false },
            { text: '', is_correct: false },
            { text: '', is_correct: false },
        ],
        addAnswer() {
            this.answers.push({ text: '', is_correct: false });
        },
        removeAnswer(index) {
            this.answers.splice(index, 1);
        }
    }
}
</script>
@endsection
