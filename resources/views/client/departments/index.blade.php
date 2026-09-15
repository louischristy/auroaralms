@extends('layouts.app')
@section('title', 'Departments — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Departments</h1>
            <p class="text-sm text-gray-500 mt-1">Manage your organization's departments.</p>
        </div>
        <a href="{{ route('manage.departments.create') }}" class="btn-primary">+ New Department</a>
    </div>

    <div class="card">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Name</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Manager</th>
                        <th class="text-center px-4 py-3 font-medium text-gray-600">Users</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($departments as $department)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $department->name }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $department->manager->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-center">{{ $department->users_count }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('manage.departments.show', $department) }}" class="text-secondary hover:text-primary text-xs font-medium">View</a>
                                    <a href="{{ route('manage.departments.edit', $department) }}" class="text-secondary hover:text-primary text-xs font-medium">Edit</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-gray-400">No departments found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($departments->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $departments->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
