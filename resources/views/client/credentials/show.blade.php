@extends('layouts.app')
@section('title', $user->name . ' — Credentials — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="space-y-6">
    <div>
        <a href="{{ route('manage.credentials.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800 flex items-center gap-1 mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Employee Credentials
        </a>
        <h1 class="text-2xl font-bold text-gray-900">{{ $user->name }}'s Credentials</h1>
        <p class="text-sm text-gray-500 mt-1">{{ $user->email }} &middot; {{ $certificates->count() }} LMS certificates, {{ $externalCredentials->count() }} external credentials</p>
    </div>

    {{-- LMS Certificates --}}
    <div>
        <h2 class="text-lg font-semibold text-gray-900 mb-3">LMS Certificates</h2>
        @if($certificates->isEmpty())
            <div class="card p-6 text-center text-gray-500 text-sm">No LMS certificates.</div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($certificates as $cert)
                    <div class="card p-5">
                        <h3 class="font-semibold text-gray-900">{{ $cert->course->title }}</h3>
                        <p class="text-sm text-gray-500 mt-1">{{ $cert->course->category }}</p>
                        <div class="flex items-center gap-4 mt-2 text-xs text-gray-400">
                            <span>Issued: {{ $cert->issued_at->format('M j, Y') }}</span>
                            @if($cert->expires_at)
                                <span class="{{ $cert->expires_at->isPast() ? 'text-red-500' : '' }}">
                                    {{ $cert->expires_at->isPast() ? 'Expired' : 'Expires' }}: {{ $cert->expires_at->format('M j, Y') }}
                                </span>
                            @endif
                        </div>
                        @if($cert->score)
                            <span class="inline-block mt-2 px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700">Score: {{ $cert->score }}%</span>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- External Credentials --}}
    <div>
        <h2 class="text-lg font-semibold text-gray-900 mb-3">External Credentials</h2>
        @if($externalCredentials->isEmpty())
            <div class="card p-6 text-center text-gray-500 text-sm">No external credentials.</div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($externalCredentials as $cred)
                    <div class="card p-5">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium text-white" style="background-color: {{ $cred->platform_color }}">
                                {{ $cred->platform_name }}
                            </span>
                            @php
                                $statusColors = [
                                    'active' => 'bg-green-100 text-green-700',
                                    'expired' => 'bg-red-100 text-red-700',
                                    'revoked' => 'bg-gray-100 text-gray-700',
                                    'pending_verification' => 'bg-yellow-100 text-yellow-700',
                                ];
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $statusColors[$cred->status] ?? 'bg-gray-100 text-gray-700' }}">
                                {{ $cred->status_label }}
                            </span>
                        </div>
                        <h3 class="font-semibold text-gray-900">{{ $cred->credential_name }}</h3>
                        @if($cred->issuer)
                            <p class="text-sm text-gray-500">{{ $cred->issuer }}</p>
                        @endif
                        <div class="flex items-center gap-4 mt-2 text-xs text-gray-400">
                            @if($cred->issued_at)
                                <span>Issued: {{ $cred->issued_at->format('M j, Y') }}</span>
                            @endif
                            @if($cred->expires_at)
                                <span class="{{ $cred->isExpired() ? 'text-red-500' : '' }}">
                                    {{ $cred->isExpired() ? 'Expired' : 'Expires' }}: {{ $cred->expires_at->format('M j, Y') }}
                                </span>
                            @endif
                        </div>
                        @if($cred->credential_url)
                            <a href="{{ $cred->credential_url }}" target="_blank" rel="noopener noreferrer" class="text-xs text-indigo-600 hover:text-indigo-800 mt-2 inline-flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                View Credential
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
