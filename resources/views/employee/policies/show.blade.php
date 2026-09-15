@extends('layouts.app')
@section('title', 'Policy — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="max-w-3xl space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $policy->title }}</h1>
            <p class="text-sm text-gray-500 mt-1">Version {{ $policy->version }}</p>
        </div>
        <a href="{{ route('learn.policies.index') }}" class="btn-outline">Back</a>
    </div>

    <div class="card p-6 space-y-4">
        <div class="prose max-w-none text-sm text-gray-800 whitespace-pre-line">{{ $policy->content }}</div>
    </div>

    <div class="card p-6 flex items-center justify-between">
        @if($acknowledged)
            <span class="badge-success">Acknowledged</span>
        @else
            <form method="POST" action="{{ route('learn.policies.acknowledge', $policy) }}">
                @csrf
                <button type="submit" class="btn-primary">Acknowledge Policy</button>
            </form>
        @endif
    </div>
</div>
@endsection
