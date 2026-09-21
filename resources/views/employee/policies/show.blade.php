@extends('layouts.app')
@section('title', $policy->title . ' — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $policy->title }}</h1>
            <p class="text-sm text-gray-500 mt-1">Version {{ $policy->version }}
                @if($policy->published_at)
                    &middot; Published {{ $policy->published_at->format('M d, Y') }}
                @endif
            </p>
        </div>
        <a href="{{ route('learn.policies.index') }}" class="btn-outline">Back</a>
    </div>

    {{-- Description --}}
    @if($policy->content)
    <div class="card p-6">
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
                <span class="text-sm font-medium text-gray-700">{{ $policy->document_original_name ?? 'Policy Document' }}</span>
            </div>
            <a href="{{ route('learn.policies.document', $policy) }}" target="_blank" class="text-sm text-blue-600 hover:underline">Open in new tab</a>
        </div>
        <object data="{{ route('learn.policies.document', $policy) }}" type="application/pdf" width="100%" style="height: 700px;">
            <div class="p-8 text-center">
                <p class="text-gray-500 mb-3">Your browser cannot display the PDF inline.</p>
                <a href="{{ route('learn.policies.document', $policy) }}" target="_blank" class="btn-primary">Download PDF</a>
            </div>
        </object>
    </div>
    @endif

    {{-- Acknowledgment --}}
    @if($policy->requires_acknowledgment)
    <div class="card p-6">
        @if($acknowledged)
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-green-100 text-green-700 font-semibold">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    Acknowledged
                </span>
                <span class="text-sm text-gray-500">You have acknowledged this policy.</span>
            </div>
        @else
            <div class="text-center space-y-3">
                <p class="text-sm text-gray-600">
                    Please read the policy document above carefully. By clicking the button below, you confirm that you have read, understood, and agree to comply with this policy.
                </p>
                @if($policy->acknowledgment_deadline_days)
                    <p class="text-xs text-amber-600 font-medium">
                        Acknowledgment deadline: {{ $policy->acknowledgment_deadline_days }} days from publication
                    </p>
                @endif
                <form method="POST" action="{{ route('learn.policies.acknowledge', $policy) }}">
                    @csrf
                    <button type="submit" class="btn-primary px-8 py-3 text-base"
                            onclick="return confirm('I confirm that I have read and understood this policy.')">
                        I Acknowledge This Policy
                    </button>
                </form>
            </div>
        @endif
    </div>
    @endif
</div>
@endsection
