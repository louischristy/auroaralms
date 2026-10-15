@extends('layouts.app')
@section('title', 'Edit Career Path — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="space-y-6" x-data="careerPathForm(@js($courses), @js($path->stages->map(fn($s) => [
    'title' => $s->title,
    'description' => $s->description ?? '',
    'required_courses' => $s->required_courses ?? [],
    'required_certifications' => $s->required_certifications ?? [],
    'estimated_duration_days' => $s->estimated_duration_days ?? '',
])->values()))">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Edit Career Path</h1>
            <p class="text-sm text-gray-500 mt-1">Update the career path and its stages.</p>
        </div>
        <a href="{{ route('manage.career-paths.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Back to Career Paths</a>
    </div>

    <form method="POST" action="{{ route('manage.career-paths.update', $path->id) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="card p-6 space-y-4">
            <h2 class="text-lg font-semibold text-gray-900">Path Details</h2>

            <div>
                <label for="title" class="label">Title</label>
                <input type="text" name="title" id="title" value="{{ old('title', $path->title) }}" class="input w-full" required>
                @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="description" class="label">Description</label>
                <textarea name="description" id="description" rows="3" class="input w-full">{{ old('description', $path->description) }}</textarea>
                @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="icon" class="label">Icon (emoji or text)</label>
                    <input type="text" name="icon" id="icon" value="{{ old('icon', $path->icon) }}" class="input w-full">
                </div>
                <div>
                    <label for="sort_order" class="label">Sort Order</label>
                    <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', $path->sort_order) }}" class="input w-full" min="0">
                </div>
                <div class="flex items-end">
                    <label class="flex items-center gap-2">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $path->is_active) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm text-gray-700">Active</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="card p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Stages / Milestones</h2>
                <button type="button" x-on:click="addStage()" class="btn-primary text-sm">+ Add Stage</button>
            </div>

            <template x-for="(stage, index) in stages" :key="index">
                <div class="border border-gray-200 rounded-lg p-4 space-y-3 relative">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-semibold text-indigo-600" x-text="'Stage ' + (index + 1)"></span>
                        <button type="button" x-on:click="removeStage(index)" class="text-red-500 hover:text-red-700 text-sm">Remove</button>
                    </div>

                    <div>
                        <label class="label">Stage Title</label>
                        <input type="text" x-bind:name="'stages[' + index + '][title]'" x-model="stage.title" class="input w-full" required>
                    </div>

                    <div>
                        <label class="label">Description</label>
                        <textarea x-bind:name="'stages[' + index + '][description]'" x-model="stage.description" rows="2" class="input w-full"></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="label">Required Courses</label>
                            <select x-bind:name="'stages[' + index + '][required_courses][]'" multiple class="input w-full" size="4">
                                <template x-for="course in availableCourses" :key="course.id">
                                    <option x-bind:value="course.id" x-text="course.title" x-bind:selected="stage.required_courses.includes(course.id)"></option>
                                </template>
                            </select>
                            <p class="text-xs text-gray-400 mt-1">Hold Ctrl/Cmd to select multiple.</p>
                        </div>
                        <div>
                            <label class="label">Required Certifications</label>
                            <div class="space-y-2">
                                <template x-for="(cert, ci) in stage.required_certifications" :key="ci">
                                    <div class="flex items-center gap-2">
                                        <input type="text" x-bind:name="'stages[' + index + '][required_certifications][]'" x-model="stage.required_certifications[ci]" class="input flex-1" placeholder="Certification name">
                                        <button type="button" x-on:click="stage.required_certifications.splice(ci, 1)" class="text-red-400 hover:text-red-600 text-sm">X</button>
                                    </div>
                                </template>
                                <button type="button" x-on:click="stage.required_certifications.push('')" class="text-sm text-indigo-600 hover:text-indigo-800">+ Add Certification</button>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="label">Estimated Duration (days)</label>
                        <input type="number" x-bind:name="'stages[' + index + '][estimated_duration_days]'" x-model="stage.estimated_duration_days" class="input w-32" min="1">
                    </div>
                </div>
            </template>

            <div x-show="stages.length === 0" class="text-center py-8 text-gray-400">
                <p>No stages added yet. Click "Add Stage" to begin building the career path.</p>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('manage.career-paths.index') }}" class="btn-secondary text-sm">Cancel</a>
            <button type="submit" class="btn-primary text-sm">Update Career Path</button>
        </div>
    </form>
</div>
@endsection
