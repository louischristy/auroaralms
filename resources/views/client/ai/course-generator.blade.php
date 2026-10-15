@extends('layouts.app')
@section('title', 'AI Course Generator')

@section('content')
<div class="max-w-5xl mx-auto py-8 px-4 sm:px-6" x-data="aiCourseGenerator" data-generate-url="{{ route('manage.ai.course-generate') }}">
    <div class="mb-6">
        <a href="{{ route('manage.ai.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">&larr; Back to AI Tools</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">AI Course Generator</h1>
        <p class="mt-1 text-sm text-gray-500">Describe a topic and let AI generate a complete training course with lessons.</p>
    </div>

    {{-- Generation Form --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
        <form @submit.prevent="generate">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Course Topic</label>
                    <input type="text" x-model="form.topic" placeholder="e.g., Project Management Fundamentals" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
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

                {{-- File Upload --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Source Document <span class="text-gray-400 font-normal">(optional)</span></label>
                    <div class="relative border-2 border-dashed rounded-lg p-6 text-center transition-colors"
                         :class="dragOver ? 'border-indigo-400 bg-indigo-50' : 'border-gray-300 hover:border-gray-400'"
                         @dragover.prevent="dragOver = true"
                         @dragleave.prevent="dragOver = false"
                         @drop.prevent="handleFileDrop($event)">
                        <template x-if="!sourceFile">
                            <div>
                                <svg class="mx-auto h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                <p class="mt-2 text-sm text-gray-600">Drag & drop a file here, or <label class="text-indigo-600 hover:text-indigo-500 cursor-pointer font-medium">browse<input type="file" class="sr-only" accept=".pdf,.docx,.txt" @change="handleFileSelect($event)"></label></p>
                                <p class="mt-1 text-xs text-gray-400">PDF, DOCX, or TXT up to 5MB</p>
                            </div>
                        </template>
                        <template x-if="sourceFile">
                            <div class="flex items-center justify-center gap-3">
                                <svg class="h-6 w-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span class="text-sm font-medium text-gray-700" x-text="sourceFile.name"></span>
                                <span class="text-xs text-gray-400" x-text="(sourceFile.size / 1024).toFixed(0) + ' KB'"></span>
                                <button type="button" @click="sourceFile = null" class="text-red-400 hover:text-red-600"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Lesson Type Configuration --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Lesson Types</label>
                    <p class="text-xs text-gray-400 mb-3">Configure how many of each lesson type to include. Total should match your lesson count.</p>
                    <div class="grid grid-cols-3 gap-4">
                        <div class="bg-gray-50 rounded-lg p-4 text-center">
                            <div class="flex items-center justify-center mb-2">
                                <svg class="h-5 w-5 text-blue-500 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span class="text-sm font-medium text-gray-700">Text</span>
                            </div>
                            <input type="number" x-model.number="lessonTypes.text" min="0" max="8" class="w-20 mx-auto text-center rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4 text-center">
                            <div class="flex items-center justify-center mb-2">
                                <svg class="h-5 w-5 text-red-500 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span class="text-sm font-medium text-gray-700">Video</span>
                            </div>
                            <input type="number" x-model.number="lessonTypes.video" min="0" max="8" class="w-20 mx-auto text-center rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4 text-center">
                            <div class="flex items-center justify-center mb-2">
                                <svg class="h-5 w-5 text-orange-500 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 13v-1m4 1v-3m4 3V8M12 21l9-9-9-9-9 9 9 9z"/></svg>
                                <span class="text-sm font-medium text-gray-700">PPT</span>
                            </div>
                            <input type="number" x-model.number="lessonTypes.ppt" min="0" max="8" class="w-20 mx-auto text-center rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>
                    <p class="mt-2 text-xs" :class="lessonTypeTotal != parseInt(form.lesson_count) ? 'text-amber-600' : 'text-gray-400'">
                        <span x-text="'Total: ' + lessonTypeTotal + ' / ' + form.lesson_count + ' lessons'"></span>
                        <span x-show="lessonTypeTotal != parseInt(form.lesson_count)"> -- counts will be adjusted automatically</span>
                    </p>
                </div>
            </div>

            {{-- Content Suggestions --}}
            <div class="mt-4 p-4 bg-indigo-50 rounded-lg" x-show="form.topic.length > 3">
                <h4 class="text-sm font-medium text-indigo-800 mb-2">Suggested Content Types</h4>
                <div class="flex flex-wrap gap-2">
                    <template x-for="suggestion in contentSuggestions" :key="suggestion.label">
                        <button type="button" @click="applySuggestion(suggestion)"
                            class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium transition-colors"
                            :class="suggestion.selected ? 'bg-indigo-600 text-white' : 'bg-white text-indigo-700 border border-indigo-200 hover:bg-indigo-100'">
                            <span x-text="suggestion.label"></span>
                        </button>
                    </template>
                </div>
            </div>

            {{-- Generation Preview --}}
            <div class="mt-4 p-4 bg-gray-50 rounded-lg border border-gray-200" x-show="form.topic.length > 3">
                <h4 class="text-sm font-medium text-gray-700 mb-2">Generation Preview</h4>
                <ul class="text-sm text-gray-600 space-y-1">
                    <li><span class="font-medium">Topic:</span> <span x-text="form.topic"></span></li>
                    <li><span class="font-medium">Difficulty:</span> <span x-text="form.difficulty" class="capitalize"></span></li>
                    <li><span class="font-medium">Audience:</span> <span x-text="form.target_audience"></span></li>
                    <li><span class="font-medium">Lessons:</span> <span x-text="form.lesson_count"></span> total
                        (<span x-text="lessonTypes.text"></span> text, <span x-text="lessonTypes.video"></span> video, <span x-text="lessonTypes.ppt"></span> ppt)</li>
                    <li x-show="sourceFile"><span class="font-medium">Source:</span> <span x-text="sourceFile?.name || 'None'"></span></li>
                </ul>
            </div>

            <div class="mt-4 flex items-center gap-3">
                <button type="submit" :disabled="loading" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg font-medium text-sm hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-wait">
                    <svg x-show="loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    <span x-text="loading ? 'Generating... (may take 30-60s)' : 'Generate Course'">Generate Course</span>
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
                    <div class="flex items-center gap-2 mb-3">
                        <p class="text-xs text-gray-400" x-text="(lesson.duration_minutes || 10) + ' min'"></p>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                              :class="{
                                  'bg-blue-100 text-blue-700': (lesson.content_type || 'text') === 'text',
                                  'bg-red-100 text-red-700': lesson.content_type === 'video',
                                  'bg-orange-100 text-orange-700': lesson.content_type === 'ppt'
                              }"
                              x-text="(lesson.content_type || 'text').toUpperCase()"></span>
                    </div>
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
