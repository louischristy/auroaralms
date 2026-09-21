@extends('layouts.app')
@section('title', 'Policy Details — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $policy->title }}</h1>
            <p class="text-sm text-gray-500 mt-1">Version {{ $policy->version }}
                @if($policy->tenant)
                    &middot; {{ $policy->tenant->name }}
                @endif
            </p>
        </div>
        <div class="flex items-center gap-2">
            @if(!$policy->is_published)
                <form method="POST" action="{{ route('manage.policies.push', $policy) }}" class="inline">
                    @csrf
                    <button type="submit" class="btn-primary" onclick="return confirm('Publish this policy and notify all users?')">
                        Publish &amp; Notify
                    </button>
                </form>
            @endif
            <a href="{{ route('manage.policies.edit', $policy) }}" class="btn-outline">Edit</a>
            <a href="{{ route('manage.policies.index') }}" class="btn-outline">Back</a>
        </div>
    </div>

    {{-- Status --}}
    <div class="card p-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            @if($policy->is_published)
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-green-100 text-green-700 text-sm font-medium">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    Published
                </span>
                <span class="text-xs text-gray-500">{{ $policy->published_at?->format('M d, Y H:i') }}</span>
            @else
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-yellow-100 text-yellow-700 text-sm font-medium">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.414L11 9.586V6z" clip-rule="evenodd"/></svg>
                    Draft
                </span>
            @endif
        </div>
        <div class="text-sm text-gray-500">
            {{ $policy->acknowledgments->count() }} / {{ $tenantUserCount }} acknowledged
        </div>
    </div>

    {{-- Description --}}
    @if($policy->content)
    <div class="card p-6">
        <h3 class="text-sm font-semibold text-gray-700 mb-2">Description</h3>
        <div class="text-sm text-gray-600">{{ $policy->content }}</div>
    </div>
    @endif

    {{-- PDF Viewer --}}
    @if($policy->document_path)
    <div class="card overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between bg-gray-50">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span class="text-sm font-medium text-gray-700">{{ $policy->document_original_name ?? 'policy.pdf' }}</span>
            </div>
            <a href="{{ route('manage.policies.document', $policy) }}" target="_blank" class="text-sm text-blue-600 hover:underline">Open in new tab</a>
        </div>
        <object data="{{ route('manage.policies.document', $policy) }}" type="application/pdf" width="100%" style="height: 600px;">
            <div class="p-8 text-center">
                <p class="text-gray-500 mb-3">Your browser cannot display the PDF inline.</p>
                <a href="{{ route('manage.policies.document', $policy) }}" target="_blank" class="btn-primary">Download PDF</a>
            </div>
        </object>
    </div>
    @endif

    {{-- Acknowledgments Table --}}
    <div class="card">
        <div class="px-4 py-3 border-b border-gray-200 font-medium text-gray-700">
            Acknowledgments ({{ $policy->acknowledgments->count() }})
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">User</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Email</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Version</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Acknowledged At</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($policy->acknowledgments as $ack)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $ack->user->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $ack->user->email ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $ack->version }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $ack->acknowledged_at?->format('M d, Y H:i') ?? $ack->acknowledged_at }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-gray-400">No acknowledgments yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Publish Button (if still draft, shown again at bottom) --}}
    @if(!$policy->is_published)
    <div class="card p-6 text-center">
        <p class="text-sm text-gray-500 mb-3">This policy is still a draft. Publish it to notify all users.</p>
        <form method="POST" action="{{ route('manage.policies.push', $policy) }}" class="inline">
            @csrf
            <button type="submit" class="btn-primary" onclick="return confirm('Publish this policy and notify all users?')">
                Publish &amp; Notify All Users
            </button>
        </form>
    </div>
    @endif
</div>
@endsection
