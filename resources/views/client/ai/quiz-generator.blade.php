@extends('layouts.app')
@section('title', 'AI Quiz Generator')

@section('content')
<div class="max-w-5xl mx-auto py-8 px-4 sm:px-6" x-data="aiQuizGenerator()">
    <div class="mb-6">
        <a href="{{ route('manage.ai.index') }}" class="text-sm text-emerald-600 hover:text-emerald-800">&larr; Back to AI Tools</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">AI Quiz Generator</h1>
        <p class="mt-1 text-sm text-gray-500">Select a course and generate quiz questions with AI. Questions will be added to the course's quiz.</p>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
        <form @submit.prevent="generate">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Select Course</label>
                    <select x-model="form.course_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500" required>
                        <option value="">-- Choose a course --</option>
                        @foreach($courses as $course)
                        <option value="{{ $course->id }}">{{ $course->title }} ({{ $course->lessons->count() }} lessons)</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Number of Questions</label>
                    <select x-model="form.question_count" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                        <option value="5">5 Questions</option>
                        <option value="10" selected>10 Questions</option>
                        <option value="15">15 Questions</option>
                        <option value="20">20 Questions</option>
                    </select>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-3">
                <button type="submit" :disabled="loading || !form.course_id" class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white rounded-lg font-medium text-sm hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-wait">
                    <svg x-show="loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    <span x-text="loading ? 'Generating...' : 'Generate Questions'"></span>
                </button>
                <span x-show="error" class="text-sm text-red-600" x-text="error"></span>
            </div>
        </form>
    </div>

    <template x-if="result">
        <div class="space-y-4">
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h2 class="text-lg font-bold text-gray-900" x-text="result.title"></h2>
                <p class="text-sm text-gray-500 mt-1" x-text="result.instructions"></p>
                <p class="text-sm text-gray-400 mt-2"><span x-text="result.questions?.length || 0"></span> questions generated</p>
            </div>

            <template x-for="(q, idx) in result.questions" :key="idx">
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <p class="font-medium text-gray-900 mb-3">
                        <span class="text-emerald-600" x-text="'Q' + (idx + 1) + '. '"></span>
                        <span x-text="q.question"></span>
                        <span class="text-xs text-gray-400 ml-2" x-text="'(' + (q.points || 10) + ' pts)'"></span>
                    </p>
                    <div class="space-y-2 ml-4 mb-3">
                        <template x-for="(a, ai) in q.answers" :key="ai">
                            <div class="flex items-center gap-2">
                                <span :class="a.is_correct ? 'text-green-600 font-semibold' : 'text-gray-600'" class="text-sm">
                                    <span x-text="String.fromCharCode(65 + ai) + ') '"></span>
                                    <span x-text="a.answer_text"></span>
                                    <template x-if="a.is_correct">
                                        <svg class="inline w-4 h-4 text-green-500 ml-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                    </template>
                                </span>
                            </div>
                        </template>
                    </div>
                    <div x-show="q.explanation" class="bg-emerald-50 rounded-lg p-3 text-sm text-emerald-800">
                        <strong>Explanation:</strong> <span x-text="q.explanation"></span>
                    </div>
                </div>
            </template>

            <div class="flex items-center gap-3">
                <form method="POST" action="{{ route('manage.ai.quiz-save') }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-green-600 text-white rounded-lg font-medium text-sm hover:bg-green-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Save Questions to Course Quiz
                    </button>
                </form>
                <button @click="result = null" class="px-4 py-2.5 border border-gray-300 text-gray-700 rounded-lg text-sm hover:bg-gray-50">Discard & Regenerate</button>
            </div>
        </div>
    </template>
</div>

<script>
function aiQuizGenerator() {
    return {
        form: { course_id: '', question_count: '10' },
        loading: false, error: '', result: null,
        async generate() {
            this.loading = true; this.error = ''; this.result = null;
            try {
                const res = await fetch('{{ route("manage.ai.quiz-generate") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: JSON.stringify(this.form)
                });
                const data = await res.json();
                if (data.success) { this.result = data.data; } else { this.error = data.error || 'Generation failed'; }
            } catch (e) { this.error = 'Network error. Please try again.'; }
            this.loading = false;
        }
    };
}
</script>
@endsection
