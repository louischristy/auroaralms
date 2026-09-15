@extends('layouts.guest')
@section('title', 'Reset Password — Auroara LMS')
@section('content')
    <h2 class="text-xl font-semibold text-gray-800 mb-2">Reset your password</h2>
    <p class="text-sm text-gray-500 mb-6">Enter your email address and we'll send you a reset link.</p>

    @if(session('status'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf
        <div>
            <label for="email" class="label">Email Address</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus class="input @error('email') border-red-500 @enderror">
            @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <button type="submit" class="btn-primary w-full">Send Reset Link</button>
        <p class="text-center text-sm"><a href="{{ route('login') }}" class="text-secondary hover:text-primary">Back to sign in</a></p>
    </form>
@endsection
