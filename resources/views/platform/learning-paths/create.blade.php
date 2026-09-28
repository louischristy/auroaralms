@extends('layouts.app')
@section('title', 'New Learning Path — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div>
        <a href="{{ route('platform.learning-paths.index') }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; Learning Paths</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-1">New Learning Path</h1>
        <p class="text-sm text-gray-500 mt-1">Group platform courses into a guided training journey.</p>
    </div>

    @include('learning-paths._form', [
        'action' => route('platform.learning-paths.store'),
        'method' => 'POST',
        'path' => null,
        'selected' => collect(),
        'cancelUrl' => route('platform.learning-paths.index'),
    ])
</div>
@endsection
