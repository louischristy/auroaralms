@extends('layouts.app')
@section('title', 'AI Course Generator')

@section('content')
<div class="max-w-5xl mx-auto py-8 px-4 sm:px-6" x-data="aiCourseGenerator" data-generate-url="{{ route('manage.ai.course-generate') }}">
    <div class="mb-6">
        <a href="{{ route('manage.ai.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">&larr; Back to AI Tools</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">AI Course Generator</h1>
        <p class="mt-1 text-sm text-gray-500">Describe a cybersecurity topic and let AI generate a complete training course with lessons.</p>
    </div>

    {{-- Generation Form --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
        <form @submit.prevent="generate">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Course Topic</label>
                    <input type="text" x-model="form.topic" placeholder="e.g., Recognizing Social Engineering Attacks" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Difficulty Level</label>
                    <select x-model="form.difficulty" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="beginner">Beginner</option>
                        <option value="intermediate">Intermediate</option>
                        <option value="advanced">Advanced</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Number of Lessons</label>
                    <select x-model="form.lesson_count" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="2">2 Lessons</option>
                        <option value="3">3 Lessons</option>
                        <option value="4" selected>4 Lessons</option>
                        <option value="5">5 Lessons</option>
                        <option value="6">6 Lessons</option>
                        <option value="8">8 Lessons</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Target Audience</label>
                    <input type="text" x-model="form.target_audience" placeholder="e.g., Non-technical office employees" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-3">
                <button type="submit" :disabled="loading" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg font-medium text-sm hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-wait">
                    <svg x-show="loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    <span x-text="loading ? 'Generating... (may take 30-60s)' : 'Generate Course'"></span>
                </button>
                <span x-show="error" class="text-sm text-red-600" x-text="error"></span>
            </div>
        </form>
    </div>

    {{-- Preview --}}
    <template x-if="result">
        <div class="space-y-6">
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900" x-text="result.title"></h2>
                        <p class="text-sm text-gray-600 mt-1" x-text="result.description"></p>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800" x-text="result.category"></span>
                </div>

                <div class="mb-4">
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">Learning Objectives</h3>
                    <ul class="list-disc pl-5 space-y-1">
                        <template x-for="obj in result.objectives" :key="obj">
                            <li class="text-sm text-gray-600" x-text="obj"></li>
                        </template>
                    </ul>
                </div>

                <p class="text-sm text-gray-500">
                    <span x-text="result.lessons?.length || 0"></span> lessons &bull; Est. <span x-text="result.duration_minutes"></span> minutes
                </p>
            </div>

            {{-- Lessons --}}
            <template x-for="(lesson, idx) in result.lessons" :key="idx">
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">
                        <span class="text-indigo-600" x-text="'Lesson ' + (idx + 1) + ': '"></span>
                        <span x-text="lesson.title"></span>
                    </h3>
                    <p class="text-xs text-gray-400 mb-3" x-text="(lesson.duration_minutes || 10) + ' min'"></p>
                    <div class="prose prose-sm max-w-none text-gray-700" x-html="lesson.content"></div>
                </div>
            </template>

            {{-- Save --}}
            <div class="flex items-center gap-3">
                <form method="POST" action="{{ route('manage.ai.course-save') }}">
                    @csrf
                    <input type="hidden" name="difficulty" :value="form.difficulty">
                    <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-green-600 text-white rounded-lg font-medium text-sm hover:bg-green-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Save Course as Draft
                    </button>
                </form>
                <button @click="result = null" class="px-4 py-2.5 border border-gray-300 text-gray-700 rounded-lg text-sm hover:bg-gray-50">Discard & Regenerate</button>
            </div>
        </div>
    </template>
</div>

@endsection
