@extends('layouts.app')
@section('title', 'Edit ' . $path->title . ' — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex items-start justify-between gap-4">
        <div>
            <a href="{{ route('manage.learning-paths.index') }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; Learning Paths</a>
            <h1 class="text-2xl font-bold text-gray-900 mt-1">Edit Learning Path</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $path->title }}</p>
        </div>
        <a href="{{ route('manage.learning-paths.assign-users', $path->id) }}" class="btn-outline text-sm">Assign Users</a>
    </div>

    @include('learning-paths._form', [
        'action' => route('manage.learning-paths.update', $path->id),
        'method' => 'PUT',
        'cancelUrl' => route('manage.learning-paths.index'),
    ])
</div>
@endsection
