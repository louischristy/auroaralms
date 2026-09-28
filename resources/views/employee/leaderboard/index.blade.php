@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Page Header --}}
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Leaderboard</h1>
        <p class="mt-1 text-sm text-gray-500">See how you rank among your colleagues</p>
    </div>

    {{-- Stats Card --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
        @foreach([
            ['Your Rank', '#'.$currentUserRank, 'bg-indigo-100 text-indigo-600'],
            ['Total Points', number_format($currentUserPoints).($period !== 'all_time' ? ' ('.str_replace('_', ' ', $period).')' : ''), 'bg-amber-100 text-amber-600'],
            ['Current Streak', $currentStreak.' day'.($currentStreak === 1 ? '' : 's').' &#128293;', 'bg-orange-100 text-orange-600'],
            ['Longest Streak', $longestStreak.' day'.($longestStreak === 1 ? '' : 's'), 'bg-rose-100 text-rose-600'],
            ['Badges Earned', $currentUserBadgesCount.' / '.$totalBadges, 'bg-emerald-100 text-emerald-600'],
        ] as [$label, $value, $color])
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <div class="inline-block rounded-full px-2 py-0.5 text-xs font-medium {{ $color }} mb-2">{{ $label }}</div>
                <p class="text-xl font-bold text-gray-900">{!! $value !!}</p>
            </div>
        @endforeach
    </div>

    {{-- Tabs --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6 border-b border-gray-200">
        <nav class="flex gap-6">
            @foreach(['points' => 'Points Leaderboard', 'badges' => 'Badge Leaderboard', 'departments' => 'Department Leaderboard'] as $key => $label)
                <a href="{{ route('learn.leaderboard.index', ['tab' => $key, 'period' => $period]) }}"
                   class="pb-3 text-sm font-medium border-b-2 {{ $tab === $key ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">{{ $label }}</a>
            @endforeach
        </nav>
        @if($tab === 'points')
            <div class="flex gap-1 pb-2">
                @foreach(['all_time' => 'All Time', 'this_month' => 'This Month', 'this_week' => 'This Week'] as $key => $label)
                    <a href="{{ route('learn.leaderboard.index', ['tab' => 'points', 'period' => $key]) }}"
                       class="px-3 py-1 rounded-full text-xs font-medium {{ $period === $key ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">{{ $label }}</a>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Main Content --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Leaderboard Table (2/3) --}}
        <div class="lg:col-span-2">
            @if($tab === 'points')
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Points Leaderboard</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <th class="px-6 py-3">Rank</th>
                                <th class="px-6 py-3">User</th>
                                <th class="px-6 py-3 text-center">Points</th>
                                <th class="px-6 py-3 text-center">Streak</th>
                                <th class="px-6 py-3 text-center">Badges</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($pointsBoard as $user)
                                <tr class="{{ $user->id === $currentUserId ? 'bg-indigo-50/50 border-l-4 border-l-indigo-500' : '' }} hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm font-semibold {{ $user->rank <= 3 ? 'text-amber-600' : 'text-gray-500' }}">{{ $user->rank }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            @if($user->avatar_path)
                                                <img src="{{ asset('storage/'.$user->avatar_path) }}" alt="" class="w-9 h-9 rounded-full object-cover">
                                            @else
                                                <div class="w-9 h-9 rounded-full bg-indigo-500 flex items-center justify-center text-sm font-bold text-white">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                                            @endif
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">{{ $user->name }}@if($user->id === $currentUserId) <span class="text-xs text-indigo-600 font-normal">(You)</span>@endif</p>
                                                <p class="text-xs text-gray-500">{{ $user->department?->name ?? '-' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center text-sm font-semibold text-gray-900">{{ number_format($user->points) }}</td>
                                    <td class="px-6 py-4 text-center text-sm text-gray-900">@if($user->current_streak > 0)&#128293; {{ $user->current_streak }}@else - @endif</td>
                                    <td class="px-6 py-4 text-center text-sm text-gray-900">{{ $user->badges_count }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-6 py-12 text-center text-gray-500">No points yet. Complete lessons and quizzes to earn some!</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @elseif($tab === 'departments')
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Department Leaderboard</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <th class="px-6 py-3">Rank</th>
                                <th class="px-6 py-3">Department</th>
                                <th class="px-6 py-3 text-center">Avg Points</th>
                                <th class="px-6 py-3 text-center">Total Badges</th>
                                <th class="px-6 py-3 text-center">Members</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($departments as $dept)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm font-semibold {{ $dept->rank <= 3 ? 'text-amber-600' : 'text-gray-500' }}">{{ $dept->rank }}</td>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $dept->name }}</td>
                                    <td class="px-6 py-4 text-center text-sm font-semibold text-gray-900">{{ number_format($dept->avg_points) }}</td>
                                    <td class="px-6 py-4 text-center text-sm text-gray-900">{{ $dept->total_badges }}</td>
                                    <td class="px-6 py-4 text-center text-sm text-gray-900">{{ $dept->member_count }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-6 py-12 text-center text-gray-500">No department data yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @else
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Badge Leaderboard</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <th class="px-6 py-3">Rank</th>
                                <th class="px-6 py-3">User</th>
                                <th class="px-6 py-3 hidden sm:table-cell">Department</th>
                                <th class="px-6 py-3 text-center">Courses</th>
                                <th class="px-6 py-3 text-center">Points</th>
                                <th class="px-6 py-3 text-center">Badges</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($leaderboard as $user)
                                <tr class="{{ $user->id === $currentUserId ? 'bg-indigo-50/50 border-l-4 border-l-indigo-500' : '' }} hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($user->rank === 1)
                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-yellow-100 text-yellow-700 font-bold text-sm">1</span>
                                        @elseif($user->rank === 2)
                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-200 text-gray-700 font-bold text-sm">2</span>
                                        @elseif($user->rank === 3)
                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-amber-100 text-amber-700 font-bold text-sm">3</span>
                                        @else
                                            <span class="inline-flex items-center justify-center w-8 h-8 text-gray-500 font-medium text-sm">{{ $user->rank }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold text-white
                                                @if($user->rank === 1) bg-yellow-500
                                                @elseif($user->rank === 2) bg-gray-400
                                                @elseif($user->rank === 3) bg-amber-600
                                                @else bg-gray-300
                                                @endif
                                            ">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">
                                                    {{ $user->name }}
                                                    @if($user->id === $currentUserId)
                                                        <span class="text-xs text-indigo-600 font-normal">(You)</span>
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden sm:table-cell">
                                        {{ $user->department?->name ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900">
                                        {{ $user->courses_completed }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="text-sm font-semibold text-gray-900">{{ number_format($user->badge_score) }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900">
                                        {{ $user->badges_count }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                        No leaderboard data yet. Start completing courses to earn points!
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>

        {{-- My Badges (1/3) --}}
        <div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">My Badges</h2>
                </div>
                <div class="p-4 grid grid-cols-2 gap-3">
                    @foreach($allBadges as $badge)
                        @php
                            $earned = $earnedBadges->has($badge->id);
                            $earnedAt = $earned ? $earnedBadges[$badge->id]->earned_at : null;
                        @endphp
                        <div class="relative rounded-xl border p-4 text-center transition-all
                            {{ $earned ? 'border-indigo-200 bg-indigo-50/50' : 'border-gray-200 bg-gray-50 opacity-50' }}">
                            <div class="text-3xl mb-2">
                                @if($earned)
                                    {{ $badge->icon }}
                                @else
                                    <svg class="w-8 h-8 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                @endif
                            </div>
                            <p class="text-xs font-semibold text-gray-900 mb-1">{{ $badge->name }}</p>
                            @if($earned)
                                <p class="text-xs text-indigo-600">{{ $earnedAt->format('M j, Y') }}</p>
                            @else
                                <p class="text-xs text-gray-500">{{ $badge->description }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
