@extends('layouts.app')
@section('title', 'Set Up Two-Factor Authentication')

@section('content')
<div class="max-w-lg mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Set Up Two-Factor Authentication</h1>
        <p class="text-sm text-gray-500 mt-1">Add an extra layer of security to your account using an authenticator app.</p>
    </div>

    <div class="card p-6 space-y-6">
        {{-- Step 1: Scan QR Code --}}
        <div>
            <h3 class="text-lg font-medium text-gray-800 mb-2">1. Scan this QR code</h3>
            <p class="text-sm text-gray-600 mb-4">
                Open your authenticator app (Google Authenticator, Microsoft Authenticator, Authy, etc.) and scan the QR code below.
            </p>

            <div class="flex justify-center p-4 bg-white rounded-lg border border-gray-200"
                 x-data="{ loaded: false }"
                 x-init="
                    const s = document.createElement('script');
                    s.src = 'https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js';
                    s.onload = () => {
                        new QRCode($refs.qr, {
                            text: {{ Js::from($qrUri) }},
                            width: 192,
                            height: 192,
                            correctLevel: QRCode.CorrectLevel.L
                        });
                        loaded = true;
                    };
                    document.head.appendChild(s);
                 ">
                <div x-ref="qr" class="w-48 h-48"></div>
            </div>
        </div>

        {{-- Manual entry --}}
        <div>
            <p class="text-sm text-gray-600 mb-2">Or enter this code manually:</p>
            <div class="flex items-center gap-2">
                <code class="flex-1 bg-gray-100 px-3 py-2 rounded text-sm font-mono text-gray-800 select-all break-all">{{ $secret }}</code>
            </div>
        </div>

        {{-- Step 2: Verify --}}
        <div>
            <h3 class="text-lg font-medium text-gray-800 mb-2">2. Enter the verification code</h3>
            <p class="text-sm text-gray-600 mb-4">
                Enter the 6-digit code from your authenticator app to verify setup.
            </p>

            <form method="POST" action="{{ route('two-factor.confirm') }}" class="space-y-4">
                @csrf
                <div>
                    <input type="text" name="code" maxlength="6" pattern="[0-9]{6}" inputmode="numeric"
                           class="input text-center text-2xl tracking-widest font-mono"
                           placeholder="000000" autofocus autocomplete="one-time-code" required>
                    @error('code')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="btn-primary w-full">Verify & Enable 2FA</button>
            </form>
        </div>
    </div>

    <div class="text-center">
        <a href="{{ route('profile.edit') }}" class="text-sm text-gray-500 hover:text-gray-700">Cancel and go back</a>
    </div>
</div>
@endsection
