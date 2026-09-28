@php
    $s = $survey ?? null;

    // Prefer the user's submitted questions after a validation failure
    if (old('questions')) {
        $initial = collect(old('questions'))->values()->map(fn ($q) => [
            'id' => $q['id'] ?? null,
            'question' => $q['question'] ?? '',
            'type' => $q['type'] ?? 'rating',
            'options' => $q['options'] ?? '',
            'is_required' => (bool) ($q['is_required'] ?? false),
        ])->all();
    } else {
        $initial = $initialQuestions ?? [];
    }
    $initial = collect($initial)->values()->map(fn ($q, $i) => $q + ['key' => 'q' . $i])->all();

    $questionTypes = [
        'rating' => 'Rating (1-5)',
        'text' => 'Free Text',
        'multiple_choice' => 'Multiple Choice',
        'yes_no' => 'Yes / No',
        'scale' => 'Scale (1-10)',
    ];
@endphp
@csrf

<div class="card p-6 space-y-5">
    <h2 class="text-lg font-semibold text-gray-900">Survey Details</h2>

    @if(!empty($tenants))
        <div>
            <label for="tenant_id" class="label">Organization *</label>
            <select id="tenant_id" name="tenant_id" class="input" required>
                <option value="">Select Tenant</option>
                @foreach($tenants as $tenant)
                    <option value="{{ $tenant->id }}" {{ old('tenant_id') == $tenant->id ? 'selected' : '' }}>{{ $tenant->name }}</option>
                @endforeach
            </select>
            @error('tenant_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    @endif

    <div>
        <label for="title" class="label">Title *</label>
        <input type="text" id="title" name="title" value="{{ old('title', $s?->title) }}" required maxlength="255" class="input" placeholder="e.g. Post-training feedback">
        @error('title')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="description" class="label">Description</label>
        <textarea id="description" name="description" rows="3" class="input" placeholder="Shown to learners at the top of the survey (optional)">{{ old('description', $s?->description) }}</textarea>
        @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label for="type" class="label">Survey Type *</label>
            <select id="type" name="type" class="input" required>
                @foreach($types as $t)
                    <option value="{{ $t }}" {{ old('type', $s?->type ?? 'post_training') === $t ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $t)) }}</option>
                @endforeach
            </select>
            @error('type')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="course_id" class="label">Linked Course <span class="text-gray-400 font-normal">(optional)</span></label>
            <select id="course_id" name="course_id" class="input">
                <option value="">Not linked to a course</option>
                @foreach($courses as $course)
                    <option value="{{ $course->id }}" {{ (string) old('course_id', $s?->course_id) === (string) $course->id ? 'selected' : '' }}>{{ $course->title }}</option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-gray-500">Linked surveys are offered to learners enrolled in the course.</p>
            @error('course_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <label class="flex items-start gap-3 p-3 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50">
            <input type="checkbox" name="is_active" value="1" class="mt-0.5 rounded border-gray-300" {{ old('is_active', $s?->is_active ?? true) ? 'checked' : '' }}>
            <span><span class="block text-sm font-medium text-gray-900">Active</span><span class="block text-xs text-gray-500">Visible to learners</span></span>
        </label>
        <label class="flex items-start gap-3 p-3 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50">
            <input type="checkbox" name="is_required" value="1" class="mt-0.5 rounded border-gray-300" {{ old('is_required', $s?->is_required) ? 'checked' : '' }}>
            <span><span class="block text-sm font-medium text-gray-900">Required</span><span class="block text-xs text-gray-500">Flagged as mandatory</span></span>
        </label>
        <label class="flex items-start gap-3 p-3 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50">
            <input type="checkbox" name="is_anonymous" value="1" class="mt-0.5 rounded border-gray-300" {{ old('is_anonymous', $s?->is_anonymous) ? 'checked' : '' }}>
            <span><span class="block text-sm font-medium text-gray-900">Anonymous</span><span class="block text-xs text-gray-500">Hide names in results</span></span>
        </label>
    </div>
</div>

{{-- Dynamic question builder --}}
<div class="card p-6"
     x-data="{
        questions: @js($initial),
        dragIndex: null,
        overIndex: null,
        armed: null,
        add() {
            this.questions.push({ id: null, key: 'n' + Date.now() + Math.random(), question: '', type: 'rating', options: '', is_required: true });
            this.$nextTick(() => {
                const inputs = this.$root.querySelectorAll('[data-question-input]');
                if (inputs.length) inputs[inputs.length - 1].focus();
            });
        },
        remove(i) { this.questions.splice(i, 1); },
        move(from, to) {
            if (from === null || to === null || from === to || to < 0 || to >= this.questions.length) return;
            const [q] = this.questions.splice(from, 1);
            this.questions.splice(to, 0, q);
        },
        drop(to) { this.move(this.dragIndex, to); this.reset(); },
        reset() { this.dragIndex = null; this.overIndex = null; this.armed = null; }
     }"
     x-init="if (questions.length === 0) add()">

    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Questions</h2>
            <p class="text-sm text-gray-500">Drag the handle (or use the arrows) to reorder.</p>
        </div>
        <span class="text-sm text-gray-500" x-text="questions.length + (questions.length === 1 ? ' question' : ' questions')"></span>
    </div>

    @if($errors->has('questions') || collect($errors->keys())->contains(fn ($k) => str_starts_with($k, 'questions.')))
        <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-700 space-y-1">
            @foreach($errors->getMessages() as $key => $messages)
                @if(str_starts_with($key, 'questions'))
                    @foreach($messages as $m)<p>{{ $m }}</p>@endforeach
                @endif
            @endforeach
        </div>
    @endif

    <div class="space-y-3">
        <template x-for="(q, index) in questions" :key="q.key">
            <div class="rounded-lg border bg-white p-4 transition-colors"
                 :class="overIndex === index && dragIndex !== index ? 'border-primary bg-primary/5' : 'border-gray-200'"
                 :draggable="armed === index ? 'true' : 'false'"
                 @dragstart="dragIndex = index; $event.dataTransfer.effectAllowed = 'move'; $event.dataTransfer.setData('text/plain', String(index))"
                 @dragover.prevent="overIndex = index"
                 @drop.prevent="drop(index)"
                 @dragend="reset()">

                <input type="hidden" :name="`questions[${index}][id]`" :value="q.id ?? ''">
                <input type="hidden" :name="`questions[${index}][is_required]`" :value="q.is_required ? 1 : 0">

                <div class="flex items-start gap-3">
                    {{-- Drag handle + arrows --}}
                    <div class="flex flex-col items-center gap-0.5 pt-1 text-gray-400">
                        <button type="button" class="hover:text-gray-700 disabled:opacity-30" :disabled="index === 0" @click="move(index, index - 1)" title="Move up">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                        </button>
                        <span class="cursor-grab active:cursor-grabbing p-0.5 hover:text-gray-700" title="Drag to reorder" @mousedown="armed = index" @mouseup="armed = null">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M7 4a1.5 1.5 0 110 3 1.5 1.5 0 010-3zm6 0a1.5 1.5 0 110 3 1.5 1.5 0 010-3zM7 8.5a1.5 1.5 0 110 3 1.5 1.5 0 010-3zm6 0a1.5 1.5 0 110 3 1.5 1.5 0 010-3zM7 13a1.5 1.5 0 110 3 1.5 1.5 0 010-3zm6 0a1.5 1.5 0 110 3 1.5 1.5 0 010-3z"/></svg>
                        </span>
                        <button type="button" class="hover:text-gray-700 disabled:opacity-30" :disabled="index === questions.length - 1" @click="move(index, index + 1)" title="Move down">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                    </div>

                    <div class="flex-1 min-w-0 space-y-3">
                        <div class="flex flex-col md:flex-row gap-3">
                            <div class="flex-1">
                                <label class="block text-xs font-medium text-gray-500 mb-1" x-text="'Question ' + (index + 1)"></label>
                                <input type="text" data-question-input class="input" x-model="q.question" :name="`questions[${index}][question]`" maxlength="1000" placeholder="Enter your question..." required>
                            </div>
                            <div class="md:w-52">
                                <label class="block text-xs font-medium text-gray-500 mb-1">Type</label>
                                <select class="input" x-model="q.type" :name="`questions[${index}][type]`">
                                    @foreach($questionTypes as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div x-show="q.type === 'multiple_choice'" x-cloak>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Options <span class="font-normal">(one per line, at least two)</span></label>
                            <textarea rows="4" class="input" x-model="q.options" :name="`questions[${index}][options]`" :disabled="q.type !== 'multiple_choice'" placeholder="Option A&#10;Option B&#10;Option C"></textarea>
                        </div>

                        <p class="text-xs text-gray-400" x-show="q.type === 'rating'">Learners pick a star rating from 1 to 5.</p>
                        <p class="text-xs text-gray-400" x-show="q.type === 'scale'">Learners pick a number from 1 to 10.</p>
                        <p class="text-xs text-gray-400" x-show="q.type === 'yes_no'">Learners answer Yes or No.</p>
                        <p class="text-xs text-gray-400" x-show="q.type === 'text'">Learners type a free-form answer.</p>
                    </div>

                    <div class="flex flex-col items-end gap-3 pt-1">
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <span class="text-xs text-gray-500">Required</span>
                            <button type="button" role="switch" :aria-checked="q.is_required" @click="q.is_required = !q.is_required"
                                    class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors"
                                    :class="q.is_required ? 'bg-primary' : 'bg-gray-300'">
                                <span class="inline-block h-4 w-4 rounded-full bg-white shadow transition-transform"
                                      :class="q.is_required ? 'translate-x-4' : 'translate-x-0.5'"></span>
                            </button>
                        </label>
                        <button type="button" class="text-red-500 hover:text-red-700 p-1" @click="remove(index)" title="Delete question">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <button type="button" @click="add()" class="mt-4 w-full flex items-center justify-center gap-2 py-3 border-2 border-dashed border-gray-300 rounded-lg text-sm font-medium text-gray-600 hover:border-primary hover:text-primary transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add Question
    </button>
</div>
