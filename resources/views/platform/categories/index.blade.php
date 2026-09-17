@extends('layouts.app')
@section('title', 'Course Categories — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Course Categories</h1>
    </div>

    {{-- Add new category --}}
    <div class="card p-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-3">Add New Category</h2>
        <form method="POST" action="{{ route('platform.categories.store') }}" class="flex items-end gap-3">
            @csrf
            <div class="flex-1">
                <label for="name" class="label">Name</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required
                       class="input @error('name') border-red-500 @enderror" placeholder="e.g. Cloud Security">
                @error('name')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="flex-1">
                <label for="description" class="label">Description (optional)</label>
                <input type="text" id="description" name="description" value="{{ old('description') }}"
                       class="input" placeholder="Short description">
            </div>
            <button type="submit" class="btn-primary text-sm whitespace-nowrap">Add Category</button>
        </form>
    </div>

    {{-- Existing categories --}}
    <div class="card overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Courses</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($categories as $category)
                    <tr x-data="{ editing: false }">
                        {{-- Display mode --}}
                        <template x-if="!editing">
                            <td class="px-6 py-4 font-medium text-gray-900" colspan="1">{{ $category->name }}</td>
                        </template>
                        <template x-if="!editing">
                            <td class="px-6 py-4 text-gray-500">{{ $category->description ?? '—' }}</td>
                        </template>

                        {{-- Edit mode --}}
                        <template x-if="editing">
                            <td class="px-6 py-4" colspan="2">
                                <form method="POST" action="{{ route('platform.categories.update', $category) }}" class="flex items-center gap-2" id="edit-form-{{ $category->id }}">
                                    @csrf
                                    @method('PUT')
                                    <input type="text" name="name" value="{{ $category->name }}" required class="input text-sm w-48">
                                    <input type="text" name="description" value="{{ $category->description }}" class="input text-sm flex-1" placeholder="Description">
                                    <label class="flex items-center gap-1 text-xs text-gray-600 whitespace-nowrap">
                                        <input type="checkbox" name="is_active" value="1" {{ $category->is_active ? 'checked' : '' }}
                                               class="rounded border-gray-300 text-primary">
                                        Active
                                    </label>
                                    <button type="submit" class="text-green-600 hover:text-green-800 text-xs font-medium">Save</button>
                                    <button type="button" @click="editing = false" class="text-gray-400 hover:text-gray-600 text-xs">Cancel</button>
                                </form>
                            </td>
                        </template>

                        <td class="px-6 py-3 text-center">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 text-gray-600 text-xs font-medium">
                                {{ $category->courses_count }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-center">
                            @if($category->is_active)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">Inactive</span>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-right space-x-2">
                            <template x-if="!editing">
                                <span>
                                    <button @click="editing = true" class="text-blue-600 hover:text-blue-800 text-xs font-medium">Edit</button>
                                    @if($category->courses_count === 0)
                                        <form method="POST" action="{{ route('platform.categories.destroy', $category) }}" class="inline" onsubmit="return confirm('Delete this category?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800 text-xs font-medium">Delete</button>
                                        </form>
                                    @endif
                                </span>
                            </template>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">No categories yet. Add one above.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
