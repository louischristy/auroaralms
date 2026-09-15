@extends('layouts.app')
@section('title', 'Policy Details — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $policy->title }}</h1>
            <p class="text-sm text-gray-500 mt-1">Version {{ $policy->version }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('manage.policies.edit', $policy) }}" class="btn-outline">Edit</a>
            <a href="{{ route('manage.policies.index') }}" class="btn-outline">Back</a>
        </div>
    </div>

    <div class="card p-6 space-y-4">
        <div>
            @if($policy->is_published)
                <span class="badge-success">Published</span>
            @else
                <span class="badge-info">Draft</span>
            @endif
        </div>
        <div class="prose max-w-none text-sm text-gray-800 whitespace-pre-line">{{ $policy->content }}</div>
    </div>

    <div class="card">
        <div class="px-4 py-3 border-b border-gray-200 font-medium text-gray-700">
            Acknowledgments ({{ $policy->acknowledgments->count() }})
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">User</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Version</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Acknowledged At</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($policy->acknowledgments as $ack)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $ack->user->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $ack->version }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $ack->acknowledged_at }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-8 text-center text-gray-400">No acknowledgments yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
