@extends('layouts.app')
@section('title', 'Department Details — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $department->name }}</h1>
            <p class="text-sm text-gray-500 mt-1">Department details.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('manage.departments.edit', $department) }}" class="btn-outline">Edit</a>
            <a href="{{ route('manage.departments.index') }}" class="btn-outline">Back</a>
        </div>
    </div>

    <div class="card p-6 space-y-4">
        <div>
            <div class="label">Description</div>
            <div class="text-gray-900">{{ $department->description ?? '—' }}</div>
        </div>
        <div>
            <div class="label">Manager</div>
            <div class="text-gray-900">{{ $department->manager->name ?? '—' }}</div>
        </div>
    </div>

    <div class="card">
        <div class="px-4 py-3 border-b border-gray-200 font-medium text-gray-700">Users ({{ $department->users->count() }})</div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Name</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Email</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($department->users as $user)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $user->name }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $user->email }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="px-4 py-8 text-center text-gray-400">No users in this department.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
