@extends('layouts.app')
@section('title', 'Edit Policy — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/quill/2.0.3/quill.snow.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/quill/2.0.3/quill.min.js"></script>

<div class="max-w-3xl space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Edit Policy</h1>
        <p class="text-sm text-gray-500 mt-1">Update {{ $policy->title }}.</p>
    </div>

    <form method="POST" action="{{ route('manage.policies.update', $policy) }}" class="card p-6 space-y-5"
          x-data="{
              content: {{ json_encode(old('content', $policy->content)) }},
              quill: null,
              init() {
                  this.quill = new Quill(this.$refs.editor, {
                      theme: 'snow',
                      modules: {
                          toolbar: [
                              [{ header: [2, 3, false] }],
                              ['bold', 'italic', 'underline'],
                              [{ list: 'ordered' }, { list: 'bullet' }],
                              ['link'],
                              ['clean']
                          ]
                      }
                  });
                  if (this.content) {
                      this.quill.root.innerHTML = this.content;
                  }
              },
              submitForm() {
                  this.content = this.quill.root.innerHTML;
              }
          }"
          x-on:submit="submitForm()">
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
            <label class="label">Content *</label>
            <input type="hidden" name="content" x-model="content">
            <div x-ref="editor" class="bg-white border border-gray-300 rounded-lg" style="min-height: 300px;"></div>
            @error('content')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
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
                <span class="text-sm text-gray-700">Requires acknowledgment</span>
            </label>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="btn-primary">Save Changes</button>
            <a href="{{ route('manage.policies.show', $policy) }}" class="btn-outline">Cancel</a>
        </div>
    </form>
</div>
@endsection
