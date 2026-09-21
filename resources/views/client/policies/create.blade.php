@extends('layouts.app')
@section('title', 'Create Policy — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="max-w-3xl space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Create Policy</h1>
        <p class="text-sm text-gray-500 mt-1">Upload a PDF document as a new policy for your organization.</p>
    </div>

    <form method="POST" action="{{ route('manage.policies.store') }}" enctype="multipart/form-data" class="card p-6 space-y-5">
        @csrf

        @if(!empty($tenants))
        <div>
            <label for="tenant_id" class="label">Assign to Tenant *</label>
            <select id="tenant_id" name="tenant_id" class="input w-full" required>
                <option value="">Select Tenant</option>
                @foreach($tenants as $tenant)
                    <option value="{{ $tenant->id }}" {{ old('tenant_id') == $tenant->id ? 'selected' : '' }}>{{ $tenant->name }}</option>
                @endforeach
            </select>
        </div>
        @endif

        <div>
            <label for="title" class="label">Title *</label>
            <input type="text" id="title" name="title" value="{{ old('title') }}" required class="input" placeholder="e.g. Acceptable Use Policy">
            @error('title')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="content" class="label">Description</label>
            <textarea id="content" name="content" rows="3" class="input w-full" placeholder="Brief summary of the policy (optional)...">{{ old('content') }}</textarea>
            @error('content')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="label">Policy Document (PDF) *</label>
            <div class="mt-1 border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                <svg class="mx-auto h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <div class="mt-2">
                    <input type="file" id="document" name="document" accept=".pdf" required
                           class="block w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                </div>
                <p class="mt-1 text-xs text-gray-500">PDF files only, max 20MB</p>
            </div>
            @error('document')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
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
                <span class="text-sm text-gray-700">Requires acknowledgment from users</span>
            </label>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="btn-primary">Create Policy</button>
            <a href="{{ route('manage.policies.index') }}" class="btn-outline">Cancel</a>
        </div>
    </form>
</div>
@endsection
