@extends('layouts.app')
@section('title', 'Policies — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Policies</h1>
            <p class="text-sm text-gray-500 mt-1">Manage organizational policies.</p>
        </div>
        <a href="{{ route('manage.policies.create') }}" class="btn-primary">+ New Policy</a>
    </div>

    <div class="card">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Title</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Version</th>
                        <th class="text-center px-4 py-3 font-medium text-gray-600">Acknowledgments</th>
                        <th class="text-center px-4 py-3 font-medium text-gray-600">Status</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($policies as $policy)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $policy->title }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $policy->version }}</td>
                            <td class="px-4 py-3 text-center">{{ $policy->acknowledgments_count }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($policy->is_published)
                                    <span class="badge-success">Published</span>
                                @else
                                    <span class="badge-info">Draft</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('manage.policies.show', $policy) }}" class="text-secondary hover:text-primary text-xs font-medium">View</a>
                                    <a href="{{ route('manage.policies.edit', $policy) }}" class="text-secondary hover:text-primary text-xs font-medium">Edit</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-400">No policies found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($policies->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $policies->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
