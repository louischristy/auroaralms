@extends('layouts.app')
@section('title', 'My Certificates — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">My Certificates</h1>
        <p class="text-sm text-gray-500 mt-1">Download certificates for completed training courses.</p>
    </div>

    @if($certificates->isEmpty())
        <div class="card p-12 text-center">
            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
            <p class="text-gray-500">No certificates yet. Complete a training course to earn your first certificate.</p>
            <a href="{{ route('learn.courses.index') }}" class="inline-block mt-4 btn-primary text-sm">Browse Courses</a>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($certificates as $cert)
                <div class="card p-5">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900">{{ $cert->course->title }}</h3>
                            <p class="text-sm text-gray-500 mt-1">{{ $cert->course->category }}</p>
                            <div class="flex items-center gap-4 mt-3 text-xs text-gray-400">
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
                        <a href="{{ route('learn.certificates.download', $cert) }}" class="btn-primary text-xs flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            PDF
                        </a>
                    </div>
                    <p class="text-xs text-gray-400 mt-2">{{ $cert->certificate_number }}</p>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
