@extends('layouts.app')
@section('title', 'Edit Session — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="max-w-3xl space-y-6">
    <div>
        <a href="{{ route('manage.virtual-classrooms.index') }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; Back to sessions</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Edit Session</h1>
    </div>

    <form method="POST" action="{{ route('manage.virtual-classrooms.update', $session->id) }}" class="card p-6 space-y-5">
        @method('PUT')
        @include('client.virtual-classrooms._form')
        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="btn-primary">Save Changes</button>
            <a href="{{ route('manage.virtual-classrooms.attendance', $session->id) }}" class="btn-secondary">Manage Attendance</a>
            <a href="{{ route('manage.virtual-classrooms.index') }}" class="btn-outline">Cancel</a>
        </div>
    </form>
</div>
@endsection
