@extends('layouts.guest')
@section('title', 'Two-Factor Authentication')

@section('content')
    <h2 class="text-xl font-semibold text-gray-800 mb-2">Two-Factor Authentication</h2>
    <p class="text-sm text-gray-500 mb-6">Enter the 6-digit code from your authenticator app, or use a recovery code.</p>

    @if(session('error'))
        <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
            {{ session('error') }}
        </div>
    @endif

    <form method="POST" action="{{ route('two-factor.verify') }}" class="space-y-5">
        @csrf

        <div>
            <label for="code" class="label">Authentication Code</label>
            <input type="text" id="code" name="code"
                   class="input text-center text-xl tracking-widest font-mono"
                   placeholder="000000" autofocus autocomplete="one-time-code" required>
            @error('code')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
            <p class="mt-2 text-xs text-gray-400">Enter your 6-digit authenticator code or a recovery code.</p>
        </div>

        <button type="submit" class="btn-primary w-full">Verify</button>
    </form>

    <div class="mt-4 text-center">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-sm text-gray-500 hover:text-gray-700">Cancel and sign out</button>
        </form>
    </div>
@endsection
