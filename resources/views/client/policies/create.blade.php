@extends('layouts.app')
@section('title', 'Create Policy — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="max-w-3xl space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Create Policy</h1>
        <p class="text-sm text-gray-500 mt-1">Publish a new policy for your organization.</p>
    </div>

    <form method="POST" action="{{ route('manage.policies.store') }}" class="card p-6 space-y-5">
        @csrf

        <div>
            <label for="title" class="label">Title *</label>
            <input type="text" id="title" name="title" value="{{ old('title') }}" required class="input">
            @error('title')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="content" class="label">Content *</label>
            <textarea id="content" name="content" rows="10" required class="input">{{ old('content') }}</textarea>
            @error('content')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="version" class="label">Version</label>
                <input type="text" id="version" name="version" value="{{ old('version', '1.0') }}" class="input">
                @error('version')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="acknowledgment_deadline_days" class="label">Acknowledgment Deadline (days)</label>
                <input type="number" id="acknowledgment_deadline_days" name="acknowledgment_deadline_days" value="{{ old('acknowledgment_deadline_days') }}" min="1" class="input">
            </div>
        </div>

        <div>
            <label class="inline-flex items-center gap-2">
                <input type="checkbox" name="requires_acknowledgment" value="1" {{ old('requires_acknowledgment', true) ? 'checked' : '' }}>
                <span class="text-sm text-gray-700">Requires acknowledgment</span>
            </label>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="btn-primary">Create Policy</button>
            <a href="{{ route('manage.policies.index') }}" class="btn-outline">Cancel</a>
        </div>
    </form>
</div>
@endsection
