@extends('layouts.app')
@section('title', 'Policies — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Policies</h1>
        <p class="text-sm text-gray-500 mt-1">Review and acknowledge company policies.</p>
    </div>

    <div class="card">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Title</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Version</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Published</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Status</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($policies as $policy)
                        @php $acked = $policy->acknowledgedByUser(Auth::user()); @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $policy->title }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $policy->version }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $policy->published_at?->format('M d, Y') ?? '—' }}</td>
                            <td class="px-4 py-3">
                                @if($acked)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-green-100 text-green-700 text-xs font-medium">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                        Acknowledged
                                    </span>
                                @elseif($policy->requires_acknowledgment)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 text-xs font-medium">
                                        Pending
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">Info only</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('learn.policies.show', $policy) }}" class="text-blue-600 hover:text-blue-800 text-xs font-medium">
                                    {{ $acked ? 'View' : 'Review & Acknowledge' }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-400">No policies to review.</td>
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
