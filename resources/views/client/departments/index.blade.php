@extends('layouts.app')
@section('title', 'Departments — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Departments</h1>
            <p class="text-sm text-gray-500 mt-1">
                @role('platform-admin')
                    Manage departments across all tenants.
                @else
                    Manage your organization's departments.
                @endrole
            </p>
        </div>
        <a href="{{ route('manage.departments.create') }}" class="btn-primary">+ New Department</a>
    </div>

    {{-- Filters --}}
    <form method="GET" class="card p-4 flex flex-col sm:flex-row gap-3 items-end">
        <div class="flex-1">
            <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name..." class="input w-full">
        </div>
        @if(isset($tenants))
            <div class="w-full sm:w-48">
                <label class="block text-xs font-medium text-gray-500 mb-1">Tenant</label>
                <select name="tenant_id" class="input w-full">
                    <option value="">All Tenants</option>
                    @foreach($tenants as $tenant)
                        <option value="{{ $tenant->id }}" {{ request('tenant_id') == $tenant->id ? 'selected' : '' }}>{{ $tenant->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif
        <div class="flex gap-2">
            <button type="submit" class="btn-primary">Filter</button>
            <a href="{{ route('manage.departments.index') }}" class="btn-secondary">Clear</a>
        </div>
    </form>

    <div class="card">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Name</th>
                        @if(isset($tenants))
                            <th class="text-left px-4 py-3 font-medium text-gray-600">Tenant</th>
                        @endif
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Manager</th>
                        <th class="text-center px-4 py-3 font-medium text-gray-600">Users</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($departments as $department)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $department->name }}</td>
                            @if(isset($tenants))
                                <td class="px-4 py-3">
                                    @if($department->tenant)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700">
                                            {{ $department->tenant->name }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                            @endif
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
                            <td colspan="{{ isset($tenants) ? 5 : 4 }}" class="px-4 py-8 text-center text-gray-400">No departments found.</td>
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
