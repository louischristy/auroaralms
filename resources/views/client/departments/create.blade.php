@extends('layouts.app')
@section('title', 'Create Department — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="max-w-2xl space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Create Department</h1>
        <p class="text-sm text-gray-500 mt-1">Add a new department to your organization.</p>
    </div>

    <form method="POST" action="{{ route('manage.departments.store') }}" class="card p-6 space-y-5">
        @csrf

        <div>
            <label for="name" class="label">Name *</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required class="input">
            @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="description" class="label">Description</label>
            <textarea id="description" name="description" rows="3" class="input">{{ old('description') }}</textarea>
            @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="manager_id" class="label">Manager</label>
            <select id="manager_id" name="manager_id" class="input">
                <option value="">— None —</option>
                @foreach($managers as $manager)
                    <option value="{{ $manager->id }}" {{ (string) old('manager_id') === (string) $manager->id ? 'selected' : '' }}>{{ $manager->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="btn-primary">Create Department</button>
            <a href="{{ route('manage.departments.index') }}" class="btn-outline">Cancel</a>
        </div>
    </form>
</div>
@endsection
