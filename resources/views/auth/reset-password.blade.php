@extends('layouts.guest')
@section('title', 'Set New Password — Auroara LMS')
@section('content')
    <h2 class="text-xl font-semibold text-gray-800 mb-6">Set a new password</h2>
    <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <div>
            <label for="email" class="label">Email Address</label>
            <input type="email" id="email" name="email" value="{{ old('email', $email) }}" required class="input">
            @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="password" class="label">New Password</label>
            <input type="password" id="password" name="password" required class="input">
            @error('password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="password_confirmation" class="label">Confirm Password</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required class="input">
        </div>
        <button type="submit" class="btn-primary w-full">Reset Password</button>
    </form>
@endsection
