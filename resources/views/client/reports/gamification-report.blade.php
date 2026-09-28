@extends('layouts.app')
@section('title', 'Gamification Report — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Gamification Report</h1>
        <div class="flex gap-3 text-sm">
            <a href="{{ route('manage.reports.export', 'gamification') }}" class="text-primary hover:underline">Export CSV</a>
            <a href="{{ route('manage.reports.index') }}" class="text-gray-500 hover:underline">{{ __('ui.back') }}</a>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="card p-4"><div class="text-xs text-gray-500">Total points awarded</div><div class="text-2xl font-bold">{{ number_format($totalPoints) }}</div></div>
        <div class="card p-4"><div class="text-xs text-gray-500">Average per user</div><div class="text-2xl font-bold">{{ $avgPerUser }}</div></div>
        <div class="card p-4"><div class="text-xs text-gray-500">Avg current streak (days)</div><div class="text-2xl font-bold">{{ round($streaks->avg_current ?? 0, 1) }}</div></div>
        <div class="card p-4"><div class="text-xs text-gray-500">Longest streak (days)</div><div class="text-2xl font-bold">{{ $streaks->best ?? 0 }}</div></div>
    </div>

    <div class="grid md:grid-cols-2 gap-6">
        <div class="card p-6">
            <h2 class="font-semibold text-gray-900 mb-3">Most active users</h2>
            <ol class="space-y-2 text-sm">
                @forelse($topUsers as $i => $u)
                    <li class="flex justify-between"><span>{{ $i + 1 }}. {{ $u->name }}</span><span class="font-medium">{{ number_format($u->total_points) }}</span></li>
                @empty
                    <li class="text-gray-500">{{ __('ui.no_results') }}</li>
                @endforelse
            </ol>
        </div>

        <div class="card p-6">
            <h2 class="font-semibold text-gray-900 mb-3">Points distribution (users by band)</h2>
            @php $dmax = max(1, max($distribution)); @endphp
            @foreach($distribution as $band => $n)
                <div class="flex items-center gap-2 mb-2 text-sm">
                    <span class="w-20 text-gray-600">{{ $band }}</span>
                    <div style="flex:1;height:10px;background:#e5e7eb;border-radius:9999px;overflow:hidden"><div style="width:{{ $n / $dmax * 100 }}%;height:100%;background:#2B4C7E"></div></div>
                    <span class="w-8 text-right">{{ $n }}</span>
                </div>
            @endforeach
        </div>

        <div class="card p-6">
            <h2 class="font-semibold text-gray-900 mb-3">Points by action</h2>
            @php $amax = max(1, (int) $byAction->max('points')); @endphp
            @forelse($byAction as $a)
                <div class="flex items-center gap-2 mb-2 text-sm">
                    <span class="w-36 truncate text-gray-600">{{ str_replace('_', ' ', $a->action) }}</span>
                    <div style="flex:1;height:10px;background:#e5e7eb;border-radius:9999px;overflow:hidden"><div style="width:{{ $a->points / $amax * 100 }}%;height:100%;background:#5BC0EB"></div></div>
                    <span class="w-12 text-right">{{ number_format($a->points) }}</span>
                </div>
            @empty
                <p class="text-sm text-gray-500">{{ __('ui.no_results') }}</p>
            @endforelse
        </div>

        <div class="card p-6">
            <h2 class="font-semibold text-gray-900 mb-3">Badge distribution</h2>
            @php $bmax = max(1, (int) $badges->max('earned')); @endphp
            @forelse($badges as $b)
                <div class="flex items-center gap-2 mb-2 text-sm">
                    <span class="w-36 truncate text-gray-600">{{ $b->icon }} {{ $b->name }}</span>
                    <div style="flex:1;height:10px;background:#e5e7eb;border-radius:9999px;overflow:hidden"><div style="width:{{ $b->earned / $bmax * 100 }}%;height:100%;background:#f59e0b"></div></div>
                    <span class="w-8 text-right">{{ $b->earned }}</span>
                </div>
            @empty
                <p class="text-sm text-gray-500">{{ __('ui.no_results') }}</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
