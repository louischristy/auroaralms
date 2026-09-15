@extends('layouts.app')
@section('title', 'Tenants — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Tenants</h1>
            <p class="text-sm text-gray-500 mt-1">Manage client organizations.</p>
        </div>
        <a href="{{ route('platform.tenants.create') }}" class="btn-primary">+ New Tenant</a>
    </div>

    <div class="card">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Organization</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Slug</th>
                        <th class="text-center px-4 py-3 font-medium text-gray-600">Users</th>
                        <th class="text-center px-4 py-3 font-medium text-gray-600">Plan</th>
                        <th class="text-center px-4 py-3 font-medium text-gray-600">Status</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($tenants as $tenant)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $tenant->name }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $tenant->slug }}</td>
                            <td class="px-4 py-3 text-center">{{ $tenant->users_count }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="badge-info">{{ ucfirst($tenant->subscription_plan) }}</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($tenant->is_active)
                                    <span class="badge-success">Active</span>
                                @else
                                    <span class="badge-danger">Inactive</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('platform.tenants.show', $tenant) }}" class="text-secondary hover:text-primary text-xs font-medium">View</a>
                                    <a href="{{ route('platform.tenants.edit', $tenant) }}" class="text-secondary hover:text-primary text-xs font-medium">Edit</a>
                                    <form method="POST" action="{{ route('platform.tenants.toggle-status', $tenant) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-xs font-medium {{ $tenant->is_active ? 'text-red-600 hover:text-red-800' : 'text-green-600 hover:text-green-800' }}">
                                            {{ $tenant->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-400">No tenants found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($tenants->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $tenants->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
