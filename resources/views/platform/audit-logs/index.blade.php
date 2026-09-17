@extends('layouts.app')
@section('title', 'Audit Logs — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Audit Logs</h1>
        <p class="text-sm text-gray-500 mt-1">Track all actions performed in the system.</p>
    </div>

    <form method="GET" class="card p-4 flex flex-col sm:flex-row gap-3 items-end flex-wrap">
        <div class="flex-1 min-w-[180px]">
            <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="User, action, IP..." class="input w-full">
        </div>
        <div class="w-full sm:w-48">
            <label class="block text-xs font-medium text-gray-500 mb-1">Action</label>
            <select name="action" class="input w-full">
                <option value="">All Actions</option>
                @foreach($actions as $action)
                    <option value="{{ $action }}" {{ ($filters['action'] ?? '') === $action ? 'selected' : '' }}>{{ str_replace('_', ' ', $action) }}</option>
                @endforeach
            </select>
        </div>
        <div class="w-full sm:w-36">
            <label class="block text-xs font-medium text-gray-500 mb-1">From</label>
            <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="input w-full">
        </div>
        <div class="w-full sm:w-36">
            <label class="block text-xs font-medium text-gray-500 mb-1">To</label>
            <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="input w-full">
        </div>
        <div class="flex gap-2">
            <button type="submit" class="btn-primary">Filter</button>
            <a href="{{ request()->url() }}" class="btn-secondary">Clear</a>
        </div>
    </form>

    <div class="card">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Time</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">User</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Action</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Subject</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">IP Address</th>
                        <th class="text-center px-4 py-3 font-medium text-gray-600"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($logs as $log)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-500 whitespace-nowrap">
                            {{ $log->created_at->format('M j, Y H:i') }}
                        </td>
                        <td class="px-4 py-3">
                            @if($log->user)
                                <span class="font-medium text-gray-900">{{ $log->user->name }}</span>
                                <span class="block text-xs text-gray-400">{{ $log->user->email }}</span>
                            @else
                                <span class="text-gray-400 italic">System</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                @if(str_contains($log->action, 'created') || str_contains($log->action, 'login'))
                                    bg-green-100 text-green-800
                                @elseif(str_contains($log->action, 'deleted'))
                                    bg-red-100 text-red-800
                                @elseif(str_contains($log->action, 'updated') || str_contains($log->action, 'changed'))
                                    bg-blue-100 text-blue-800
                                @else
                                    bg-gray-100 text-gray-800
                                @endif
                            ">
                                {{ str_replace('_', ' ', $log->action) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            @if($log->model_type)
                                {{ class_basename($log->model_type) }} #{{ $log->model_id }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-500 text-xs font-mono">
                            {{ $log->ip_address ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route(auth()->user()->isPlatformAdmin() ? 'platform.audit-logs.show' : 'manage.audit-logs.show', $log) }}" class="text-blue-600 hover:text-blue-800 text-xs">Details</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-400">No audit logs found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $logs->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
