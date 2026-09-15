@extends('layouts.guest')

@section('title', 'Sign In — Auroara LMS')

@section('content')
    <h2 class="text-xl font-semibold text-gray-800 mb-6">Sign in to your account</h2>

    @if(session('status'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <label for="email" class="label">Email Address</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                   class="input @error('email') border-red-500 @enderror">
            @error('email')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="label">Password</label>
            <input type="password" id="password" name="password" required
                   class="input @error('password') border-red-500 @enderror">
            @error('password')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 text-sm text-gray-600">
                <input type="checkbox" name="remember" class="rounded border-gray-300 text-primary focus:ring-primary">
                Remember me
            </label>
            <a href="{{ route('password.request') }}" class="text-sm text-secondary hover:text-primary">Forgot password?</a>
        </div>

        <button type="submit" class="btn-primary w-full">Sign In</button>
    </form>
@endsection
