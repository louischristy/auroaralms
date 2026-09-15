@extends('layouts.app')
@section('title', 'Profile — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="max-w-2xl space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Profile</h1>
        <p class="text-sm text-gray-500 mt-1">Manage your account details.</p>
    </div>

    <form method="POST" action="{{ route('profile.update') }}" class="card p-6 space-y-5">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="name" class="label">Name *</label>
                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required class="input">
                @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="phone" class="label">Phone</label>
                <input type="text" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" class="input">
                @error('phone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <label for="job_title" class="label">Job Title</label>
            <input type="text" id="job_title" name="job_title" value="{{ old('job_title', $user->job_title) }}" class="input">
            @error('job_title')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="btn-primary">Save Changes</button>
        </div>
    </form>

    <form method="POST" action="{{ route('profile.password') }}" class="card p-6 space-y-5">
        @csrf
        @method('PUT')

        <h3 class="text-lg font-medium text-gray-800">Change Password</h3>

        <div>
            <label for="current_password" class="label">Current Password *</label>
            <input type="password" id="current_password" name="current_password" required class="input">
            @error('current_password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="password" class="label">New Password *</label>
                <input type="password" id="password" name="password" required class="input">
                @error('password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="password_confirmation" class="label">Confirm New Password *</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required class="input">
            </div>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="btn-primary">Change Password</button>
        </div>
    </form>
</div>
@endsection
