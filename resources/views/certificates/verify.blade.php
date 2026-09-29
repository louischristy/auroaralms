@extends('layouts.public')
@section('title', 'Verify Certificate — Auroara LMS')

@section('content')
<div class="space-y-6">
    <div class="text-center">
        <h1 class="text-2xl font-bold text-gray-900">Verify a Certificate</h1>
        <p class="text-sm text-gray-500 mt-1">Enter the verification code found on any Auroara LMS certificate.</p>
    </div>

    <div class="card p-6">
        <form method="POST" action="{{ route('certificates.verify') }}">
            @csrf
            <div class="flex gap-3">
                <input
                    type="text"
                    name="code"
                    value="{{ $code ?? old('code') }}"
                    placeholder="AUR-XXXX-XXXX"
                    class="input flex-1 text-center text-lg tracking-widest uppercase"
                    maxlength="14"
                    required
                >
                <button type="submit" class="btn-primary px-6">Verify</button>
            </div>
        </form>
    </div>

    @if(session('error'))
        <div class="card p-4 border-red-200 bg-red-50 text-red-700 text-sm">
            {{ session('error') }}
        </div>
    @endif

    @if(isset($searched))
        @if($certificate)
            <div class="card p-6 border-green-200">
                <div class="flex items-center gap-3 mb-4">
                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Valid Certificate
                    </span>
                    @if($certificate->expires_at && $certificate->expires_at->isPast())
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-amber-100 text-amber-700">
                            Expired
                        </span>
                    @endif
                </div>

                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between border-b border-gray-100 pb-2">
                        <dt class="font-medium text-gray-500">Certificate Holder</dt>
                        <dd class="text-gray-900 font-semibold">{{ $certificate->user->name }}</dd>
                    </div>
                    <div class="flex justify-between border-b border-gray-100 pb-2">
                        <dt class="font-medium text-gray-500">Course</dt>
                        <dd class="text-gray-900">{{ $certificate->course->title }}</dd>
                    </div>
                    <div class="flex justify-between border-b border-gray-100 pb-2">
                        <dt class="font-medium text-gray-500">Date Issued</dt>
                        <dd class="text-gray-900">{{ $certificate->issued_at->format('F j, Y') }}</dd>
                    </div>
                    @if($certificate->expires_at)
                        <div class="flex justify-between border-b border-gray-100 pb-2">
                            <dt class="font-medium text-gray-500">Valid Until</dt>
                            <dd class="text-gray-900 {{ $certificate->expires_at->isPast() ? 'text-red-600' : '' }}">
                                {{ $certificate->expires_at->format('F j, Y') }}
                            </dd>
                        </div>
                    @endif
                    @if($certificate->score)
                        <div class="flex justify-between border-b border-gray-100 pb-2">
                            <dt class="font-medium text-gray-500">Score</dt>
                            <dd class="text-gray-900">{{ $certificate->score }}%</dd>
                        </div>
                    @endif
                    <div class="flex justify-between border-b border-gray-100 pb-2">
                        <dt class="font-medium text-gray-500">Issuing Organization</dt>
                        <dd class="text-gray-900">{{ $certificate->course->tenant->name ?? 'Auroara LMS' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="font-medium text-gray-500">Verification Code</dt>
                        <dd class="text-gray-900 font-mono">{{ $certificate->verification_code }}</dd>
                    </div>
                </dl>
            </div>
        @else
            <div class="card p-6 border-red-200">
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-sm font-semibold bg-red-100 text-red-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Certificate Not Found
                    </span>
                </div>
                <p class="text-sm text-gray-500 mt-3">
                    No valid certificate was found for code <strong class="font-mono">{{ $code }}</strong>.
                    Please check the code and try again.
                </p>
            </div>
        @endif
    @endif
</div>
@endsection
