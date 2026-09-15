@extends('layouts.app')
@section('title', 'Edit User — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="max-w-2xl space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Edit User</h1>
        <p class="text-sm text-gray-500 mt-1">Update {{ $user->name }}'s details.</p>
    </div>

    <form method="POST" action="{{ route('manage.users.update', $user) }}" class="card p-6 space-y-5">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="name" class="label">Name *</label>
                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required class="input">
                @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="email" class="label">Email *</label>
                <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required class="input">
                @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="department_id" class="label">Department</label>
                <select id="department_id" name="department_id" class="input">
                    <option value="">— None —</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" {{ (string) old('department_id', $user->department_id) === (string) $department->id ? 'selected' : '' }}>{{ $department->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="job_title" class="label">Job Title</label>
                <input type="text" id="job_title" name="job_title" value="{{ old('job_title', $user->job_title) }}" class="input">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="employee_id" class="label">Employee ID</label>
                <input type="text" id="employee_id" name="employee_id" value="{{ old('employee_id', $user->employee_id) }}" class="input">
            </div>
            <div>
                <label for="role" class="label">Role *</label>
                <select id="role" name="role" required class="input">
                    @php $currentRole = old('role', optional($user->roles->first())->name); @endphp
                    <option value="employee" {{ $currentRole === 'employee' ? 'selected' : '' }}>Employee</option>
                    <option value="manager" {{ $currentRole === 'manager' ? 'selected' : '' }}>Manager</option>
                    <option value="client-admin" {{ $currentRole === 'client-admin' ? 'selected' : '' }}>Client Admin</option>
                </select>
            </div>
        </div>

        <div>
            <label class="inline-flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                <span class="text-sm text-gray-700">Active</span>
            </label>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="btn-primary">Save Changes</button>
            <a href="{{ route('manage.users.show', $user) }}" class="btn-outline">Cancel</a>
        </div>
    </form>
</div>
@endsection
