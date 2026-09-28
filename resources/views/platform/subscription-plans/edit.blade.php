@extends('layouts.app')
@section('title', 'Edit Plan — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="max-w-6xl space-y-6">
    <div>
        <a href="{{ route('platform.subscription-plans.index') }}" class="text-sm text-gray-500 hover:text-primary">&larr; Pricing Plans</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-1">Edit Plan: {{ $plan->name }}</h1>
        <p class="text-sm text-gray-500 mt-1">Changes to pricing apply to subscriptions that do not override the value.</p>
    </div>
    <form method="POST" action="{{ route('platform.subscription-plans.update', $plan->id) }}">
        @method('PUT')
        @include('platform.subscription-plans._form', ['submitLabel' => 'Save Changes'])
    </form>
</div>
@endsection
