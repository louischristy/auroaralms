@extends('layouts.app')
@section('title', 'New Phishing Campaign — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="space-y-6" x-data="{ selectedTemplate: null, target: 'all' }">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">New Phishing Campaign</h1>
            <p class="text-sm text-gray-500 mt-1">Configure and launch a phishing simulation.</p>
        </div>
        <a href="{{ route('manage.phishing.index') }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; Back to Campaigns</a>
    </div>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('manage.phishing.store') }}" class="space-y-6">
        @csrf

        <div class="card p-6 space-y-4">
            <h2 class="text-lg font-semibold text-gray-900">Campaign Details</h2>

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Campaign Name</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required class="input w-full" placeholder="e.g., Q1 2024 Phishing Test">
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description (optional)</label>
                <textarea name="description" id="description" rows="2" class="input w-full" placeholder="Brief description of this campaign...">{{ old('description') }}</textarea>
            </div>
        </div>

        <div class="card p-6 space-y-4">
            <h2 class="text-lg font-semibold text-gray-900">Email Template</h2>

            <div>
                <label for="template_id" class="block text-sm font-medium text-gray-700 mb-1">Select Template</label>
                <select name="template_id" id="template_id" required class="input w-full" x-on:change="selectedTemplate = $event.target.selectedOptions[0].dataset.preview || null">
                    <option value="">Choose a template...</option>
                    @foreach($templates as $template)
                        <option value="{{ $template->id }}"
                                data-preview="{{ e($template->subject) }}"
                                data-type="{{ $template->scenario_type }}"
                                data-difficulty="{{ $template->difficulty }}"
                                {{ old('template_id') == $template->id ? 'selected' : '' }}>
                            {{ $template->name }} ({{ ucfirst($template->difficulty) }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div x-show="selectedTemplate" x-cloak class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm text-gray-500 mb-1">Subject preview:</p>
                <p class="text-sm font-medium text-gray-800" x-text="selectedTemplate"></p>
            </div>

            <div class="flex flex-wrap gap-4 text-sm text-gray-500">
                @foreach($templates as $template)
                    <div class="hidden" data-template-info="{{ $template->id }}">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700">{{ str_replace('_', ' ', ucfirst($template->scenario_type)) }}</span>
                        @php
                            $dc = ['easy' => 'bg-green-50 text-green-700', 'medium' => 'bg-yellow-50 text-yellow-700', 'hard' => 'bg-red-50 text-red-700'];
                        @endphp
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $dc[$template->difficulty] }}">{{ ucfirst($template->difficulty) }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card p-6 space-y-4">
            <h2 class="text-lg font-semibold text-gray-900">Target Audience</h2>

            <div class="flex gap-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="target" value="all" x-model="target" class="text-primary focus:ring-primary" {{ old('target', 'all') === 'all' ? 'checked' : '' }}>
                    <span class="text-sm text-gray-700">All employees</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="target" value="departments" x-model="target" class="text-primary focus:ring-primary" {{ old('target') === 'departments' ? 'checked' : '' }}>
                    <span class="text-sm text-gray-700">Specific departments</span>
                </label>
            </div>

            <div x-show="target === 'departments'" x-cloak class="space-y-2">
                <label class="block text-sm font-medium text-gray-700">Select Departments</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @foreach($departments as $dept)
                        <label class="flex items-center gap-2 p-2 rounded border border-gray-200 hover:bg-gray-50 cursor-pointer">
                            <input type="checkbox" name="department_ids[]" value="{{ $dept->id }}" class="text-primary focus:ring-primary rounded">
                            <span class="text-sm text-gray-700">{{ $dept->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card p-6 space-y-4">
            <h2 class="text-lg font-semibold text-gray-900">Simulation Settings</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="custom_click_rate" class="block text-sm font-medium text-gray-700 mb-1">Custom Click Rate % (optional)</label>
                    <input type="number" name="custom_click_rate" id="custom_click_rate" value="{{ old('custom_click_rate') }}" min="0" max="100" class="input w-full" placeholder="Auto (based on template difficulty)">
                    <p class="text-xs text-gray-400 mt-1">Override the % of users who click the phishing link.</p>
                </div>
                <div>
                    <label for="custom_report_rate" class="block text-sm font-medium text-gray-700 mb-1">Custom Report Rate % (optional)</label>
                    <input type="number" name="custom_report_rate" id="custom_report_rate" value="{{ old('custom_report_rate') }}" min="0" max="100" class="input w-full" placeholder="Auto (based on template difficulty)">
                    <p class="text-xs text-gray-400 mt-1">Override the % of users who report the phishing email.</p>
                </div>
            </div>

            <div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="training_aware" value="1" {{ old('training_aware', '1') ? 'checked' : '' }}
                           class="rounded border-gray-300 text-primary focus:ring-primary">
                    <span class="text-sm text-gray-700">Training-aware simulation</span>
                </label>
                <p class="text-xs text-gray-400 mt-1 ml-6">Users who completed phishing/social engineering courses will show better results (less clicking, more reporting).</p>
            </div>
        </div>

        <div class="card p-6 space-y-4">
            <h2 class="text-lg font-semibold text-gray-900">Schedule</h2>

            <div>
                <label for="scheduled_at" class="block text-sm font-medium text-gray-700 mb-1">Schedule Date & Time (optional)</label>
                <input type="datetime-local" name="scheduled_at" id="scheduled_at" value="{{ old('scheduled_at') }}" class="input w-full sm:w-64">
                <p class="text-xs text-gray-400 mt-1">Leave empty to save as draft.</p>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('manage.phishing.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">Create Campaign</button>
        </div>
    </form>
</div>
@endsection
