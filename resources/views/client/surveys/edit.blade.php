@extends('layouts.app')
@section('title', 'Edit Survey — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="max-w-4xl space-y-6">
    <div>
        <a href="{{ route('manage.surveys.index') }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; Back to surveys</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Edit Survey</h1>
    </div>

    <form method="POST" action="{{ route('manage.surveys.update', $survey->id) }}" class="space-y-6">
        @method('PUT')
        @include('client.surveys._form')
        <div class="flex items-center gap-3">
            <button type="submit" class="btn-primary">Save Changes</button>
            <a href="{{ route('manage.surveys.index') }}" class="btn-outline">Cancel</a>
        </div>
    </form>
</div>
@endsection
