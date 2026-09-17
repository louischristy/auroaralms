@extends('layouts.app')
@section('title', 'Audit Log Detail — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="max-w-2xl space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Audit Log Detail</h1>
            <p class="text-sm text-gray-500 mt-1">Log entry #{{ $log->id }}</p>
        </div>
        <a href="{{ url()->previous() }}" class="btn-outline btn-sm">&larr; Back</a>
    </div>

    <div class="card p-6 space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase">Action</p>
                <p class="mt-1 font-medium text-gray-900">{{ str_replace('_', ' ', $log->action) }}</p>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase">Timestamp</p>
                <p class="mt-1 text-gray-900">{{ $log->created_at->format('M j, Y \a\t H:i:s') }}</p>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase">User</p>
                @if($log->user)
                    <p class="mt-1 text-gray-900">{{ $log->user->name }}</p>
                    <p class="text-xs text-gray-500">{{ $log->user->email }}</p>
                @else
                    <p class="mt-1 text-gray-400 italic">System</p>
                @endif
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase">IP Address</p>
                <p class="mt-1 font-mono text-sm text-gray-900">{{ $log->ip_address ?? '—' }}</p>
            </div>
            @if($log->model_type)
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase">Subject</p>
                <p class="mt-1 text-gray-900">{{ class_basename($log->model_type) }} #{{ $log->model_id }}</p>
            </div>
            @endif
        </div>

        @if($log->user_agent)
        <div class="pt-4 border-t border-gray-100">
            <p class="text-xs font-medium text-gray-500 uppercase">User Agent</p>
            <p class="mt-1 text-xs text-gray-600 font-mono break-all">{{ $log->user_agent }}</p>
        </div>
        @endif
    </div>

    @if($log->old_values || $log->new_values)
    <div class="card p-6 space-y-4">
        <h3 class="text-lg font-medium text-gray-800">Changes</h3>

        @if($log->old_values && $log->new_values)
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-left px-4 py-2 font-medium text-gray-600">Field</th>
                            <th class="text-left px-4 py-2 font-medium text-gray-600">Before</th>
                            <th class="text-left px-4 py-2 font-medium text-gray-600">After</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($log->new_values as $field => $newVal)
                        <tr>
                            <td class="px-4 py-2 font-medium text-gray-700">{{ str_replace('_', ' ', $field) }}</td>
                            <td class="px-4 py-2 text-red-700 bg-red-50">
                                {{ is_array($log->old_values[$field] ?? null) ? json_encode($log->old_values[$field]) : ($log->old_values[$field] ?? '—') }}
                            </td>
                            <td class="px-4 py-2 text-green-700 bg-green-50">
                                {{ is_array($newVal) ? json_encode($newVal) : $newVal }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @elseif($log->new_values)
            <div class="bg-gray-50 rounded-lg p-4">
                <pre class="text-xs text-gray-700 whitespace-pre-wrap">{{ json_encode($log->new_values, JSON_PRETTY_PRINT) }}</pre>
            </div>
        @elseif($log->old_values)
            <p class="text-sm text-gray-600 mb-2">Deleted record values:</p>
            <div class="bg-gray-50 rounded-lg p-4">
                <pre class="text-xs text-gray-700 whitespace-pre-wrap">{{ json_encode($log->old_values, JSON_PRETTY_PRINT) }}</pre>
            </div>
        @endif
    </div>
    @endif
</div>
@endsection
