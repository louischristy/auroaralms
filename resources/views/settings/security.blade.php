@extends('layouts.app')

@section('title', 'Security Settings')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Security Settings</h1>
        <p class="mt-1 text-sm text-gray-600">Manage your account security, two-factor authentication, and password.</p>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="rounded-lg bg-green-50 border border-green-200 p-4">
            <p class="text-sm text-green-800">{{ session('success') }}</p>
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-lg bg-red-50 border border-red-200 p-4">
            <p class="text-sm text-red-800">{{ session('error') }}</p>
        </div>
    @endif

    @if(session('info'))
        <div class="rounded-lg bg-blue-50 border border-blue-200 p-4">
            <p class="text-sm text-blue-800">{{ session('info') }}</p>
        </div>
    @endif

    {{-- Two-Factor Authentication --}}
    <div class="card">
        <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900">Two-Factor Authentication</h2>
            <p class="mt-1 text-sm text-gray-600">Add an extra layer of security to your account using a time-based one-time password (TOTP).</p>

            <div class="mt-4">
                @if($twoFactorEnabled)
                    <div class="flex items-center gap-2 mb-4">
                        <span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-sm font-medium text-green-800">
                            <svg class="mr-1.5 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.06l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                            </svg>
                            Enabled
                        </span>
                    </div>

                    {{-- Recovery Codes --}}
                    <div class="mb-6">
                        <h3 class="text-sm font-medium text-gray-900 mb-2">Recovery Codes</h3>
                        <p class="text-sm text-gray-600 mb-3">Keep these codes in a safe place. Each code can only be used once to access your account if you lose your authenticator device.</p>
                        <div class="bg-gray-50 rounded-lg p-4 font-mono text-sm">
                            @foreach($recoveryCodes as $code)
                                <div class="py-0.5">{{ substr($code, 0, 3) }}*****{{ substr($code, -2) }}</div>
                            @endforeach
                            @if(empty($recoveryCodes))
                                <p class="text-gray-500 italic">No recovery codes available. Please regenerate them.</p>
                            @endif
                        </div>
                    </div>

                    {{-- Regenerate Recovery Codes --}}
                    <form method="POST" action="{{ route('two-factor.recovery-codes') }}" class="mb-4">
                        @csrf
                        <div class="mb-3">
                            <label for="regenerate_password" class="label">Confirm Password to Regenerate Codes</label>
                            <input type="password" name="current_password" id="regenerate_password" class="input mt-1" required>
                            @error('current_password', 'regenerate')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <button type="submit" class="btn-secondary">Regenerate Recovery Codes</button>
                    </form>

                    {{-- Disable 2FA --}}
                    @if($tenantRequires2fa)
                        <div class="rounded-lg bg-yellow-50 border border-yellow-200 p-4">
                            <p class="text-sm text-yellow-800">Your organization requires two-factor authentication. Contact your administrator to change this policy.</p>
                        </div>
                    @else
                        <div class="border-t pt-4 mt-4">
                            <h3 class="text-sm font-medium text-gray-900 mb-2">Disable Two-Factor Authentication</h3>
                            <p class="text-sm text-gray-600 mb-3">This will remove the extra security layer from your account.</p>
                            <form method="POST" action="{{ route('two-factor.disable') }}">
                                @csrf
                                <div class="mb-3">
                                    <label for="disable_password" class="label">Confirm Password</label>
                                    <input type="password" name="current_password" id="disable_password" class="input mt-1" required>
                                    @error('current_password', 'disable')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <button type="submit" class="btn-secondary text-red-600 hover:text-red-700">Disable Two-Factor Authentication</button>
                            </form>
                        </div>
                    @endif
                @else
                    <div class="flex items-center gap-2 mb-4">
                        <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm font-medium text-gray-800">
                            <svg class="mr-1.5 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                            </svg>
                            Not Enabled
                        </span>
                    </div>
                    <p class="text-sm text-gray-600 mb-4">Two-factor authentication adds an additional layer of security to your account by requiring a code from your authenticator app when you sign in.</p>
                    <a href="{{ route('two-factor.setup') }}" class="btn-primary">Enable Two-Factor Authentication</a>
                @endif
            </div>
        </div>
    </div>

    {{-- Change Password --}}
    <div class="card">
        <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900">Change Password</h2>
            <p class="mt-1 text-sm text-gray-600">Ensure your account uses a strong, unique password.</p>

            <form method="POST" action="{{ route('settings.security.password') }}" class="mt-4 space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="current_password" class="label">Current Password</label>
                    <input type="password" name="current_password" id="current_password" class="input mt-1" required>
                    @error('current_password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="label">New Password</label>
                    <input type="password" name="password" id="password" class="input mt-1" required>
                    <p class="mt-1 text-xs text-gray-500">Minimum 8 characters with uppercase, lowercase, numbers, and symbols.</p>
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="label">Confirm New Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="input mt-1" required>
                </div>

                <div>
                    <button type="submit" class="btn-primary">Update Password</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Active Sessions Link --}}
    <div class="card">
        <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900">Active Sessions</h2>
            <p class="mt-1 text-sm text-gray-600">View and manage your active browser sessions.</p>
            <div class="mt-4">
                <a href="{{ route('sessions.index') }}" class="btn-secondary">Manage Sessions</a>
            </div>
        </div>
    </div>
</div>
@endsection
