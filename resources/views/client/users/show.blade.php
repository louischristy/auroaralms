@extends('layouts.app')
@section('title', 'User Details — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $user->name }}</h1>
            <p class="text-sm text-gray-500 mt-1">User details.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('manage.users.edit', $user) }}" class="btn-outline">Edit</a>
            <a href="{{ route('manage.users.index') }}" class="btn-outline">Back</a>
        </div>
    </div>

    <div class="card p-6 space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div>
                <div class="label">Email</div>
                <div class="text-gray-900">{{ $user->email }}</div>
            </div>
            <div>
                <div class="label">Department</div>
                <div class="text-gray-900">{{ $user->department->name ?? '—' }}</div>
            </div>
            <div>
                <div class="label">Job Title</div>
                <div class="text-gray-900">{{ $user->job_title ?? '—' }}</div>
            </div>
            <div>
                <div class="label">Employee ID</div>
                <div class="text-gray-900">{{ $user->employee_id ?? '—' }}</div>
            </div>
            <div>
                <div class="label">Role</div>
                <div>
                    @foreach($user->roles as $role)
                        <span class="badge-info">{{ $role->name }}</span>
                    @endforeach
                </div>
            </div>
            <div>
                <div class="label">Status</div>
                <div>
                    @if($user->is_active)
                        <span class="badge-success">Active</span>
                    @else
                        <span class="badge-danger">Inactive</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
