@extends('layouts.app')
@section('title', 'Tenant Details — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $tenant->name }}</h1>
            <p class="text-sm text-gray-500 mt-1">Tenant details.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('platform.tenants.sso.index', $tenant) }}" class="btn-outline">SSO Settings</a>
            <a href="{{ route('platform.tenants.edit', $tenant) }}" class="btn-outline">Edit</a>
            <a href="{{ route('platform.tenants.index') }}" class="btn-outline">Back</a>
        </div>
    </div>

    <div class="card p-6 space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div>
                <div class="label">Slug</div>
                <div class="text-gray-900">{{ $tenant->slug }}</div>
            </div>
            <div>
                <div class="label">Domain</div>
                <div class="text-gray-900">{{ $tenant->domain ?? '—' }}</div>
            </div>
            <div>
                <div class="label">Subscription Plan</div>
                <div><span class="badge-info">{{ ucfirst($tenant->subscription_plan) }}</span></div>
            </div>
            <div>
                <div class="label">Status</div>
                <div>
                    @if($tenant->is_active)
                        <span class="badge-success">Active</span>
                    @else
                        <span class="badge-danger">Inactive</span>
                    @endif
                </div>
            </div>
            <div>
                <div class="label">Max Users</div>
                <div class="text-gray-900">{{ $tenant->max_users }}</div>
            </div>
            <div>
                <div class="label">Users</div>
                <div class="text-gray-900">{{ $tenant->users_count ?? $tenant->users()->count() }}</div>
            </div>
            <div>
                <div class="label">Require 2FA</div>
                <div>
                    @if($tenant->require_two_factor)
                        <span class="badge-success">Required</span>
                    @else
                        <span class="badge-warning">Optional</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Tenant Users --}}
    <div class="card">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-lg font-medium text-gray-800">Users ({{ $tenant->users->count() }})</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Name</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Email</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Role</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Department</th>
                        <th class="text-center px-4 py-3 font-medium text-gray-600">Status</th>
                        <th class="text-center px-4 py-3 font-medium text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($tenant->users as $user)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $user->name }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $user->email }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $user->roles->pluck('name')->map(fn($r) => ucwords(str_replace('-', ' ', $r)))->implode(', ') ?: '—' }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $user->department->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($user->is_active)
                                    <span class="badge-success">Active</span>
                                @else
                                    <span class="badge-danger">Inactive</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <form method="POST" action="{{ route('platform.tenants.reset-user-password', [$tenant, $user]) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-xs text-blue-600 hover:text-blue-800 font-medium" title="Send password reset email">Reset Password</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-400">No users in this tenant.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Security Settings --}}
    <div class="card p-6 space-y-4">
        <h3 class="text-lg font-medium text-gray-800">Security Settings</h3>
        <form method="POST" action="{{ route('platform.tenants.update', $tenant) }}">
            @csrf
            @method('PUT')
            <input type="hidden" name="_security_only" value="1">
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="require_two_factor" value="1"
                       {{ $tenant->require_two_factor ? 'checked' : '' }}
                       class="rounded border-gray-300 text-primary focus:ring-primary">
                <div>
                    <span class="text-sm font-medium text-gray-700">Require Two-Factor Authentication</span>
                    <p class="text-xs text-gray-500">All users in this tenant will be required to set up 2FA before accessing the platform.</p>
                </div>
            </label>
            <div class="pt-3">
                <button type="submit" class="btn-primary btn-sm">Save Security Settings</button>
            </div>
        </form>
    </div>
</div>
@endsection
