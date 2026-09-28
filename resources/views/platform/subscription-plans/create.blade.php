@extends('layouts.app')
@section('title', 'Create Plan — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="max-w-6xl space-y-6">
    <div>
        <a href="{{ route('platform.subscription-plans.index') }}" class="text-sm text-gray-500 hover:text-primary">&larr; Pricing Plans</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-1">Create Plan</h1>
        <p class="text-sm text-gray-500 mt-1">Define pricing, limits, features and modules for a new subscription plan.</p>
    </div>
    <form method="POST" action="{{ route('platform.subscription-plans.store') }}">
        @include('platform.subscription-plans._form', ['submitLabel' => 'Create Plan'])
    </form>
</div>
@endsection
