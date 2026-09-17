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

    {{-- Active Sessions --}}
    <div class="card p-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-medium text-gray-800">Active Sessions</h3>
                <p class="text-sm text-gray-500 mt-1">Manage your logged-in devices and sessions.</p>
            </div>
            <a href="{{ route('sessions.index') }}" class="btn-outline btn-sm">View Sessions &rarr;</a>
        </div>
    </div>

    {{-- Two-Factor Authentication --}}
    <div class="card p-6 space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-medium text-gray-800">Two-Factor Authentication</h3>
                <p class="text-sm text-gray-500 mt-1">Add an extra layer of security to your account.</p>
            </div>
            @if($user->two_factor_enabled)
                <span class="badge-success">Enabled</span>
            @else
                <span class="badge-warning">Not Enabled</span>
            @endif
        </div>

        @if($user->two_factor_enabled)
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <p class="text-sm text-green-800">Two-factor authentication is active on your account. You will be asked for a verification code each time you sign in.</p>
            </div>

            <div class="flex flex-wrap gap-3">
                <form method="POST" action="{{ route('two-factor.recovery-codes') }}" class="inline">
                    @csrf
                    <input type="password" name="current_password" placeholder="Current password" required class="input w-48 inline-block text-sm">
                    <button type="submit" class="btn-outline btn-sm ml-1">Regenerate Recovery Codes</button>
                </form>
            </div>

            @if(!($user->tenant && $user->tenant->require_two_factor))
            <form method="POST" action="{{ route('two-factor.disable') }}" class="pt-2 border-t border-gray-100">
                @csrf
                <p class="text-sm text-gray-600 mb-3">To disable two-factor authentication, enter your password.</p>
                <div class="flex items-center gap-3">
                    <input type="password" name="current_password" placeholder="Current password" required class="input w-48 text-sm">
                    <button type="submit" class="btn-danger btn-sm">Disable 2FA</button>
                </div>
                @error('current_password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </form>
            @else
            <p class="text-sm text-amber-700 pt-2 border-t border-gray-100">Your organization requires two-factor authentication. It cannot be disabled.</p>
            @endif
        @else
            <a href="{{ route('two-factor.setup') }}" class="btn-primary inline-block">Set Up 2FA</a>
        @endif
    </div>
</div>
@endsection
