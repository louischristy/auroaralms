@extends('layouts.app')
@section('title', 'Users — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Users</h1>
            <p class="text-sm text-gray-500 mt-1">Manage your organization's users.</p>
        </div>
        <a href="{{ route('manage.users.create') }}" class="btn-primary">+ Add User</a>
    </div>

    <form method="GET" class="card p-4 flex flex-col sm:flex-row gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or email" class="input flex-1">
        <select name="department_id" class="input sm:w-56">
            <option value="">All Departments</option>
            @foreach($departments as $department)
                <option value="{{ $department->id }}" {{ (string) request('department_id') === (string) $department->id ? 'selected' : '' }}>{{ $department->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn-primary">Search</button>
    </form>

    <div class="card">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Name</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Email</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Department</th>
                        <th class="text-center px-4 py-3 font-medium text-gray-600">Status</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $user->name }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $user->email }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $user->department->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($user->is_active)
                                    <span class="badge-success">Active</span>
                                @else
                                    <span class="badge-danger">Inactive</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('manage.users.show', $user) }}" class="text-secondary hover:text-primary text-xs font-medium">View</a>
                                    <a href="{{ route('manage.users.edit', $user) }}" class="text-secondary hover:text-primary text-xs font-medium">Edit</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-400">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
