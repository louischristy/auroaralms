@extends('layouts.app')
@section('title', 'Create Policy — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/quill/2.0.3/quill.snow.min.css">

<div class="max-w-3xl space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Create Policy</h1>
        <p class="text-sm text-gray-500 mt-1">Publish a new policy for your organization.</p>
    </div>

    {{-- Import from Document --}}
    <div class="card p-6 space-y-3">
        <h3 class="text-sm font-medium text-gray-700">Import from Document</h3>
        <p class="text-xs text-gray-500">Upload a PDF or Word document to auto-fill the policy content below.</p>
        <form method="POST" action="{{ route('manage.policies.import-document') }}" enctype="multipart/form-data" class="flex items-end gap-3">
            @csrf
            <div class="flex-1">
                <input type="file" name="document" accept=".pdf,.doc,.docx" required class="block w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            </div>
            <button type="submit" class="btn-outline btn-sm whitespace-nowrap">Import</button>
        </form>
    </div>

    <form id="policy-form" method="POST" action="{{ route('manage.policies.store') }}" class="card p-6 space-y-5">
        @csrf

        @if(!empty($tenants))
        <div>
            <label for="tenant_id" class="label">Assign to Tenant *</label>
            <select id="tenant_id" name="tenant_id" class="input w-full" required>
                <option value="">Select Tenant</option>
                @foreach($tenants as $tenant)
                    <option value="{{ $tenant->id }}">{{ $tenant->name }}</option>
                @endforeach
            </select>
        </div>
        @endif

        <div>
            <label for="title" class="label">Title *</label>
            <input type="text" id="title" name="title" value="{{ old('title', $importedTitle ?? '') }}" required class="input">
            @error('title')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="label">Content *</label>
            <div id="policy-editor-container" class="border border-gray-300 rounded-lg overflow-hidden" style="min-height: 300px; display: none;"></div>
            <textarea id="policy-content" name="content" rows="12" required
                      class="input w-full" placeholder="Enter policy content (HTML supported)...">{{ old('content', $importedContent ?? '') }}</textarea>
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

<script src="https://cdnjs.cloudflare.com/ajax/libs/quill/2.0.3/quill.min.js"></script>
<script src="{{ asset('js/policy-editor.js') }}"></script>
@endsection
