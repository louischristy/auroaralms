@extends('layouts.app')
@section('title', 'New Subscription — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="max-w-6xl space-y-6">
    <div>
        <a href="{{ route('platform.subscriptions.index') }}" class="text-sm text-gray-500 hover:text-primary">&larr; Subscriptions</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-1">New Subscription</h1>
        <p class="text-sm text-gray-500 mt-1">Assign a plan to a tenant and optionally customize its pricing.</p>
    </div>
    <form method="POST" action="{{ route('platform.subscriptions.store') }}">
        @include('platform.subscriptions._form', ['submitLabel' => 'Create Subscription'])
    </form>
</div>
@endsection
