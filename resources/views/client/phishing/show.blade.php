@extends('layouts.app')
@section('title', $campaign->name . ' — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $campaign->name }}</h1>
            <p class="text-sm text-gray-500 mt-1">
                Template: {{ $campaign->template->name ?? '—' }} &middot;
                Created by {{ $campaign->creator->name ?? 'Unknown' }} on {{ $campaign->created_at->format('M d, Y') }}
            </p>
        </div>
        <div class="flex items-center gap-3">
            @if($campaign->status !== 'completed')
                <form method="POST" action="{{ route('manage.phishing.simulate', $campaign) }}">
                    @csrf
                    <button type="submit" class="btn-primary" onclick="return confirm('This will simulate sending phishing emails to all employees and generate randomized results. Continue?')">
                        Run Simulation
                    </button>
                </form>
            @endif
            <a href="{{ route('manage.phishing.index') }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; Back</a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 rounded-lg px-4 py-3 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm">{{ session('error') }}</div>
    @endif

    {{-- Status Badge --}}
    <div class="flex items-center gap-3">
        @php
            $colors = ['draft' => 'bg-gray-100 text-gray-700', 'scheduled' => 'bg-yellow-100 text-yellow-700', 'active' => 'bg-blue-100 text-blue-700', 'completed' => 'bg-green-100 text-green-700'];
        @endphp
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $colors[$campaign->status] ?? 'bg-gray-100 text-gray-700' }}">
            {{ ucfirst($campaign->status) }}
        </span>
        @if($campaign->sent_at)
            <span class="text-sm text-gray-500">Sent {{ $campaign->sent_at->format('M d, Y g:i A') }}</span>
        @endif
    </div>

    @if($campaign->description)
        <div class="card p-4">
            <p class="text-sm text-gray-600">{{ $campaign->description }}</p>
        </div>
    @endif

    @if($total > 0)
        {{-- Summary Stats --}}
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
            @php
                $stats = [
                    ['label' => 'Sent', 'value' => $total, 'color' => 'text-gray-700', 'bg' => 'bg-gray-50'],
                    ['label' => 'Opened', 'value' => $statusCounts['opened'] + $statusCounts['clicked'] + $statusCounts['submitted'] + $statusCounts['reported'], 'color' => 'text-blue-700', 'bg' => 'bg-blue-50'],
                    ['label' => 'Clicked', 'value' => $statusCounts['clicked'] + $statusCounts['submitted'], 'color' => 'text-orange-700', 'bg' => 'bg-orange-50'],
                    ['label' => 'Submitted Data', 'value' => $statusCounts['submitted'], 'color' => 'text-red-700', 'bg' => 'bg-red-50'],
                    ['label' => 'Reported', 'value' => $statusCounts['reported'], 'color' => 'text-green-700', 'bg' => 'bg-green-50'],
                ];
            @endphp
            @foreach($stats as $stat)
                <div class="card p-4 {{ $stat['bg'] }}">
                    <p class="text-xs font-medium {{ $stat['color'] }} uppercase">{{ $stat['label'] }}</p>
                    <p class="text-2xl font-bold {{ $stat['color'] }} mt-1">{{ $stat['value'] }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ $total > 0 ? round($stat['value'] / $total * 100) : 0 }}%</p>
                </div>
            @endforeach
        </div>

        {{-- Donut Chart --}}
        <div class="card p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Result Breakdown</h2>
            <div class="flex flex-col sm:flex-row items-center gap-8">
                <div class="flex-shrink-0">
                    @php
                        $radius = 60;
                        $circumference = 2 * M_PI * $radius;
                        $segments = [
                            ['count' => $statusCounts['sent'], 'color' => '#9ca3af', 'label' => 'Sent (no open)'],
                            ['count' => $statusCounts['opened'], 'color' => '#3b82f6', 'label' => 'Opened only'],
                            ['count' => $statusCounts['clicked'], 'color' => '#f97316', 'label' => 'Clicked link'],
                            ['count' => $statusCounts['submitted'], 'color' => '#ef4444', 'label' => 'Submitted data'],
                            ['count' => $statusCounts['reported'], 'color' => '#22c55e', 'label' => 'Reported'],
                        ];
                        $offset = 0;
                    @endphp
                    <svg width="160" height="160" viewBox="0 0 160 160">
                        @foreach($segments as $seg)
                            @if($seg['count'] > 0)
                                @php
                                    $pct = $seg['count'] / $total;
                                    $dashLen = $pct * $circumference;
                                    $dashGap = $circumference - $dashLen;
                                @endphp
                                <circle cx="80" cy="80" r="{{ $radius }}" fill="none"
                                        stroke="{{ $seg['color'] }}" stroke-width="24"
                                        stroke-dasharray="{{ $dashLen }} {{ $dashGap }}"
                                        stroke-dashoffset="{{ -$offset }}"
                                        transform="rotate(-90 80 80)" />
                                @php $offset += $dashLen; @endphp
                            @endif
                        @endforeach
                        <text x="80" y="76" text-anchor="middle" class="text-2xl font-bold" fill="#1f2937" font-size="24" font-weight="bold">{{ $total }}</text>
                        <text x="80" y="96" text-anchor="middle" fill="#9ca3af" font-size="12">total</text>
                    </svg>
                </div>
                <div class="flex flex-col gap-2">
                    @foreach($segments as $seg)
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full flex-shrink-0" style="background: {{ $seg['color'] }}"></span>
                            <span class="text-sm text-gray-600">{{ $seg['label'] }}: <strong>{{ $seg['count'] }}</strong> ({{ $total > 0 ? round($seg['count'] / $total * 100) : 0 }}%)</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Per-User Results Table --}}
        <div class="card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Individual Results</h2>
            </div>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email Sent</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Opened</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Clicked</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reported</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($results as $result)
                        <tr>
                            <td class="px-6 py-3 whitespace-nowrap">
                                <p class="text-sm font-medium text-gray-900">{{ $result->user->name ?? 'Unknown' }}</p>
                                <p class="text-xs text-gray-400">{{ $result->user->email ?? '' }}</p>
                            </td>
                            <td class="px-6 py-3 whitespace-nowrap">
                                @php
                                    $sc = ['sent' => 'bg-gray-100 text-gray-700', 'opened' => 'bg-blue-100 text-blue-700', 'clicked' => 'bg-orange-100 text-orange-700', 'submitted' => 'bg-red-100 text-red-700', 'reported' => 'bg-green-100 text-green-700'];
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $sc[$result->status] ?? 'bg-gray-100 text-gray-700' }}">
                                    {{ ucfirst($result->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-3 whitespace-nowrap text-xs text-gray-500">{{ $result->email_sent_at?->format('g:i A') ?? '—' }}</td>
                            <td class="px-6 py-3 whitespace-nowrap text-xs text-gray-500">{{ $result->email_opened_at?->format('g:i A') ?? '—' }}</td>
                            <td class="px-6 py-3 whitespace-nowrap text-xs text-gray-500">{{ $result->link_clicked_at?->format('g:i A') ?? '—' }}</td>
                            <td class="px-6 py-3 whitespace-nowrap text-xs text-gray-500">{{ $result->reported_at?->format('g:i A') ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="card p-12 text-center">
            <div class="text-gray-400 mb-2">
                <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <p class="text-lg font-medium text-gray-500">No results yet</p>
            <p class="text-sm text-gray-400 mt-1">Run a simulation to generate phishing test results.</p>
        </div>
    @endif
</div>
@endsection
