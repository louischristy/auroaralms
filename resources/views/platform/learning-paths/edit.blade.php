@extends('layouts.app')
@section('title', 'Edit ' . $path->title . ' — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex items-start justify-between gap-4">
        <div>
            <a href="{{ route('platform.learning-paths.index') }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; Learning Paths</a>
            <h1 class="text-2xl font-bold text-gray-900 mt-1">Edit Learning Path</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $path->title }}</p>
        </div>
    </div>

    @include('learning-paths._form', [
        'action' => route('platform.learning-paths.update', $path->id),
        'method' => 'PUT',
        'cancelUrl' => route('platform.learning-paths.index'),
    ])

    {{-- Tenant assignment --}}
    <form method="POST" action="{{ route('platform.learning-paths.assign-tenants', $path->id) }}" class="card">
        @csrf
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-700">Assign to Tenants</h3>
            <p class="text-xs text-gray-500 mt-1">Tenants selected here can see this path and assign it to their users. Its courses are made available to them as well.</p>
        </div>
        <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-72 overflow-y-auto">
            @forelse($tenants as $tenant)
                <label class="flex items-center gap-2 p-2 rounded hover:bg-gray-50 cursor-pointer">
                    <input type="checkbox" name="tenant_ids[]" value="{{ $tenant->id }}" class="rounded border-gray-300"
                           {{ in_array($tenant->id, $assignedTenantIds) ? 'checked' : '' }}>
                    <span class="text-sm text-gray-700">{{ $tenant->name }}</span>
                </label>
            @empty
                <p class="text-sm text-gray-400">No tenants yet.</p>
            @endforelse
        </div>
        <div class="px-6 py-4 border-t border-gray-100">
            <button type="submit" class="btn-primary text-sm">Save Tenant Assignments</button>
        </div>
    </form>

    {{-- Danger zone --}}
    <div class="card p-6 flex items-center justify-between gap-4">
        <div>
            <h3 class="text-sm font-semibold text-gray-700">Delete this path</h3>
            <p class="text-xs text-gray-500 mt-1">The path is archived and hidden from tenants and learners.</p>
        </div>
        <form method="POST" action="{{ route('platform.learning-paths.destroy', $path->id) }}" x-data
              x-on:submit="if (!confirm('Delete this learning path?')) $event.preventDefault()">
            @csrf @method('DELETE')
            <button type="submit" class="btn-danger text-sm">Delete</button>
        </form>
    </div>
</div>
@endsection
