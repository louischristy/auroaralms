@extends('layouts.app')
@section('title', __('My Credentials') . ' — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ __('My Credentials') }}</h1>
            <p class="text-sm text-gray-500 mt-1">{{ __('All your certificates, credentials, and achievements in one place.') }}</p>
        </div>
        <a href="{{ route('learn.credentials.create') }}" class="btn-primary text-sm flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            {{ __('Add External Credential') }}
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Tab navigation --}}
    <div class="border-b border-gray-200">
        <nav class="flex gap-6" aria-label="Tabs">
            <a href="{{ route('learn.credentials.index') }}{{ request('tab') !== 'external' && request('tab') !== 'achievements' ? '' : '?tab=lms' }}"
               class="pb-3 px-1 text-sm font-medium border-b-2 {{ request('tab', 'lms') === 'lms' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                {{ __('LMS Certificates') }}
                <span class="ml-1 px-2 py-0.5 rounded-full text-xs {{ request('tab', 'lms') === 'lms' ? 'bg-indigo-100 text-indigo-600' : 'bg-gray-100 text-gray-500' }}">{{ $certificates->count() }}</span>
            </a>
            <a href="{{ route('learn.credentials.index', ['tab' => 'external']) }}"
               class="pb-3 px-1 text-sm font-medium border-b-2 {{ request('tab') === 'external' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                {{ __('External Credentials') }}
                <span class="ml-1 px-2 py-0.5 rounded-full text-xs {{ request('tab') === 'external' ? 'bg-indigo-100 text-indigo-600' : 'bg-gray-100 text-gray-500' }}">{{ $externalCredentials->count() }}</span>
            </a>
            <a href="{{ route('learn.credentials.index', ['tab' => 'achievements']) }}"
               class="pb-3 px-1 text-sm font-medium border-b-2 {{ request('tab') === 'achievements' ? 'border-amber-500 text-amber-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                {{ __('Achievements') }}
                <span class="ml-1 px-2 py-0.5 rounded-full text-xs {{ request('tab') === 'achievements' ? 'bg-amber-100 text-amber-600' : 'bg-gray-100 text-gray-500' }}">{{ $earnedBadges->count() }}/{{ $badges->count() }}</span>
            </a>
        </nav>
    </div>

    {{-- LMS Certificates tab --}}
    @if(request('tab', 'lms') === 'lms')
        @if($certificates->isEmpty())
            <div class="card p-12 text-center">
                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                <p class="text-gray-500">{{ __('No certificates yet. Complete a training course to earn your first certificate.') }}</p>
                <a href="{{ route('learn.courses.index') }}" class="inline-block mt-4 btn-primary text-sm">{{ __('Browse Courses') }}</a>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($certificates as $cert)
                    <div class="card p-5">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-700">{{ __('LMS') }}</span>
                                    <h3 class="font-semibold text-gray-900">{{ $cert->course->title }}</h3>
                                </div>
                                <p class="text-sm text-gray-500 mt-1">{{ $cert->course->category }}</p>
                                <div class="flex items-center gap-4 mt-3 text-xs text-gray-400">
                                    <span>{{ __('Issued') }}: {{ $cert->issued_at->format('M j, Y') }}</span>
                                    @if($cert->expires_at)
                                        <span class="{{ $cert->expires_at->isPast() ? 'text-red-500' : '' }}">
                                            {{ $cert->expires_at->isPast() ? __('Expired') : __('Expires') }}: {{ $cert->expires_at->format('M j, Y') }}
                                        </span>
                                    @endif
                                </div>
                                @if($cert->score)
                                    <span class="inline-block mt-2 px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700">{{ __('Score') }}: {{ $cert->score }}%</span>
                                @endif
                            </div>
                            <a href="{{ route('learn.certificates.download', $cert) }}" class="btn-primary text-xs flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                PDF
                            </a>
                        </div>
                        <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-100">
                            <p class="text-xs text-gray-400 font-mono">{{ $cert->certificate_number }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    @endif

    {{-- External Credentials tab --}}
    @if(request('tab') === 'external')
        @if($externalCredentials->isEmpty())
            <div class="card p-12 text-center">
                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                <p class="text-gray-500">{{ __('No external credentials added yet. Track certifications from Coursera, Udemy, AWS, and more.') }}</p>
                <a href="{{ route('learn.credentials.create') }}" class="inline-block mt-4 btn-primary text-sm">{{ __('Add Credential') }}</a>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($externalCredentials as $cred)
                    <div class="card p-5">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
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
                                <div class="flex items-center gap-4 mt-3 text-xs text-gray-400">
                                    @if($cred->issued_at)
                                        <span>{{ __('Issued') }}: {{ $cred->issued_at->format('M j, Y') }}</span>
                                    @endif
                                    @if($cred->expires_at)
                                        <span class="{{ $cred->isExpired() ? 'text-red-500' : '' }}">
                                            {{ $cred->isExpired() ? __('Expired') : __('Expires') }}: {{ $cred->expires_at->format('M j, Y') }}
                                        </span>
                                    @endif
                                </div>
                                @if($cred->credential_id)
                                    <p class="text-xs text-gray-400 font-mono mt-1">ID: {{ $cred->credential_id }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-100">
                            <div class="flex items-center gap-2">
                                @if($cred->credential_url)
                                    <a href="{{ $cred->credential_url }}" target="_blank" rel="noopener noreferrer" class="text-xs text-indigo-600 hover:text-indigo-800 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                        {{ __('View Credential') }}
                                    </a>
                                @endif
                                @if($cred->certificate_file)
                                    <a href="{{ route('learn.credentials.download', $cred) }}" class="text-xs text-indigo-600 hover:text-indigo-800 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        {{ __('Download') }}
                                    </a>
                                @endif
                            </div>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('learn.credentials.edit', $cred) }}" class="text-xs text-gray-500 hover:text-gray-700">{{ __('Edit') }}</a>
                                <form method="POST" action="{{ route('learn.credentials.destroy', $cred) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-red-500 hover:text-red-700" onclick="return confirm('{{ __('Are you sure you want to delete this credential?') }}')">{{ __('Delete') }}</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    @endif

    {{-- Achievements tab --}}
    @if(request('tab') === 'achievements')
        @php
            $statMapping = [
                'first_completion' => 'courses_completed',
                'courses_completed' => 'courses_completed',
                'quizzes_passed' => 'quizzes_passed',
                'perfect_scores' => 'perfect_scores',
                'phishing_reporter' => 'phishing_reports',
            ];
            $iconMapping = [
                'first_completion' => '🎯',
                'courses_completed' => '📚',
                'quizzes_passed' => '✅',
                'perfect_scores' => '💯',
                'phishing_reporter' => '🎣',
                'streak_days' => '🔥',
            ];
        @endphp

        {{-- Stats summary --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="card p-4 text-center">
                <p class="text-3xl font-bold text-indigo-600">{{ $stats['courses_completed'] }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ __('Courses Completed') }}</p>
            </div>
            <div class="card p-4 text-center">
                <p class="text-3xl font-bold text-green-600">{{ $stats['quizzes_passed'] }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ __('Quizzes Passed') }}</p>
            </div>
            <div class="card p-4 text-center">
                <p class="text-3xl font-bold text-amber-600">{{ $stats['perfect_scores'] }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ __('Perfect Scores') }}</p>
            </div>
            <div class="card p-4 text-center">
                <p class="text-3xl font-bold text-rose-600">{{ $earnedBadges->count() }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ __('Badges Earned') }}</p>
            </div>
        </div>

        {{-- Earned badges --}}
        @if($earnedBadges->isNotEmpty())
        <div>
            <h2 class="text-lg font-semibold text-gray-900 mb-3">{{ __('Earned Badges') }}</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($badges as $badge)
                    @if($earnedBadges->has($badge->id))
                        @php $earned = $earnedBadges[$badge->id]; @endphp
                        <div class="card p-5 border-2 border-amber-200 bg-gradient-to-br from-amber-50 to-white">
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0 w-14 h-14 bg-amber-100 rounded-2xl flex items-center justify-center text-2xl shadow-sm">
                                    {{ $iconMapping[$badge->criteria_type] ?? '🏆' }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-semibold text-gray-900">{{ $badge->name }}</h3>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ $badge->description }}</p>
                                    <p class="text-xs text-amber-600 font-medium mt-2">
                                        {{ __('Earned') }} {{ $earned->earned_at->format('M j, Y') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
        @endif

        {{-- Unearned badges with progress --}}
        @php $unearnedBadges = $badges->filter(fn($b) => !$earnedBadges->has($b->id)); @endphp
        @if($unearnedBadges->isNotEmpty())
        <div>
            <h2 class="text-lg font-semibold text-gray-900 mb-3">{{ __('In Progress') }}</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($unearnedBadges as $badge)
                    @php
                        $statKey = $statMapping[$badge->criteria_type] ?? null;
                        $current = $statKey ? ($stats[$statKey] ?? 0) : 0;
                        $target = $badge->criteria_value;
                        $pct = $target > 0 ? min(100, round(($current / $target) * 100)) : 0;
                    @endphp
                    <div class="card p-5 opacity-80 hover:opacity-100 transition">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-14 h-14 bg-gray-100 rounded-2xl flex items-center justify-center text-2xl grayscale">
                                {{ $iconMapping[$badge->criteria_type] ?? '🏆' }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-semibold text-gray-700">{{ $badge->name }}</h3>
                                <p class="text-xs text-gray-400 mt-0.5">{{ $badge->description }}</p>
                                @if($statKey)
                                <div class="mt-3">
                                    <div class="flex items-center justify-between text-xs mb-1">
                                        <span class="text-gray-500">{{ $current }}/{{ $target }}</span>
                                        <span class="font-medium text-indigo-600">{{ $pct }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-indigo-500 h-2 rounded-full transition-all" style="width: {{ $pct }}%"></div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        @if($badges->isEmpty())
            <div class="card p-12 text-center">
                <p class="text-4xl mb-4">🏆</p>
                <p class="text-gray-500">{{ __('No badges are configured yet. Complete courses to start earning achievements!') }}</p>
            </div>
        @endif
    @endif
</div>
@endsection
