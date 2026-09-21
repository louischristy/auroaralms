@extends('layouts.app')
@section('title', 'Edit Policy — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="max-w-3xl space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Edit Policy</h1>
        <p class="text-sm text-gray-500 mt-1">Update {{ $policy->title }}.</p>
    </div>

    <form method="POST" action="{{ route('manage.policies.update', $policy) }}" enctype="multipart/form-data" class="card p-6 space-y-5">
        @csrf
        @method('PUT')

        @if(!empty($tenants))
        <div>
            <label for="tenant_id" class="label">Tenant</label>
            <select id="tenant_id" name="tenant_id" class="input w-full">
                @foreach($tenants as $tenant)
                    <option value="{{ $tenant->id }}" {{ $policy->tenant_id == $tenant->id ? 'selected' : '' }}>{{ $tenant->name }}</option>
                @endforeach
            </select>
        </div>
        @endif

        <div>
            <label for="title" class="label">Title *</label>
            <input type="text" id="title" name="title" value="{{ old('title', $policy->title) }}" required class="input">
            @error('title')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="content" class="label">Description</label>
            <textarea id="content" name="content" rows="3" class="input w-full" placeholder="Brief summary of the policy (optional)...">{{ old('content', $policy->content) }}</textarea>
            @error('content')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="label">Policy Document (PDF)</label>
            @if($policy->document_path)
                <div class="mb-3 p-3 bg-gray-50 rounded-lg flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span class="text-sm font-medium text-gray-700">{{ $policy->document_original_name ?? 'policy.pdf' }}</span>
                    </div>
                    <a href="{{ route('manage.policies.document', $policy) }}" target="_blank" class="text-sm text-blue-600 hover:underline">View PDF</a>
                </div>
            @endif
            <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center">
                <input type="file" name="document" accept=".pdf"
                       class="block w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                <p class="mt-1 text-xs text-gray-500">{{ $policy->document_path ? 'Upload a new PDF to replace the current one' : 'PDF files only, max 20MB' }}</p>
            </div>
            @error('document')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="version" class="label">Version *</label>
                <input type="text" id="version" name="version" value="{{ old('version', $policy->version) }}" required class="input">
                @error('version')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="acknowledgment_deadline_days" class="label">Acknowledgment Deadline (days)</label>
                <input type="number" id="acknowledgment_deadline_days" name="acknowledgment_deadline_days" value="{{ old('acknowledgment_deadline_days', $policy->acknowledgment_deadline_days) }}" min="1" class="input">
            </div>
        </div>

        <div>
            <label class="inline-flex items-center gap-2">
                <input type="checkbox" name="requires_acknowledgment" value="1" {{ old('requires_acknowledgment', $policy->requires_acknowledgment) ? 'checked' : '' }}>
                <span class="text-sm text-gray-700">Requires acknowledgment from users</span>
            </label>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="btn-primary">Save Changes</button>
            <a href="{{ route('manage.policies.show', $policy) }}" class="btn-outline">Cancel</a>
        </div>
    </form>
</div>
@endsection
