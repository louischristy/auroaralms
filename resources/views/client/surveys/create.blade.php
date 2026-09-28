@extends('layouts.app')
@section('title', 'New Survey — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="max-w-4xl space-y-6">
    <div>
        <a href="{{ route('manage.surveys.index') }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; Back to surveys</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">New Survey</h1>
    </div>

    <form method="POST" action="{{ route('manage.surveys.store') }}" class="space-y-6">
        
        @include('client.surveys._form')
        <div class="flex items-center gap-3">
            <button type="submit" class="btn-primary">Create Survey</button>
            <a href="{{ route('manage.surveys.index') }}" class="btn-outline">Cancel</a>
        </div>
    </form>
</div>
@endsection
