@extends('layouts.app')
@section('title', 'Recovery Codes')

@section('content')
<div class="max-w-lg mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Recovery Codes</h1>
        <p class="text-sm text-gray-500 mt-1">Save these codes in a safe place. Each code can only be used once.</p>
    </div>

    <div class="card p-6 space-y-4">
        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
            <div class="flex gap-3">
                <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
                <div>
                    <p class="text-sm font-medium text-amber-800">Save these recovery codes</p>
                    <p class="text-sm text-amber-700 mt-1">
                        If you lose access to your authenticator app, you can use one of these codes to sign in. Each code can only be used once.
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-gray-50 rounded-lg p-4">
            <div class="grid grid-cols-2 gap-2">
                @foreach($recoveryCodes as $code)
                    <code class="text-sm font-mono text-gray-700 bg-white px-3 py-1.5 rounded border border-gray-200 text-center">{{ $code }}</code>
                @endforeach
            </div>
        </div>

        <div class="flex gap-3 pt-2">
            <a href="{{ route('profile.edit') }}" class="btn-primary flex-1 text-center">Done</a>
        </div>
    </div>
</div>
@endsection
