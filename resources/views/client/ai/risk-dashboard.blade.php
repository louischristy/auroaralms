@extends('layouts.app')
@section('title', 'Smart Risk Scoring')

@section('content')
<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6">
    <div class="mb-6">
        <a href="{{ route('manage.ai.index') }}" class="text-sm text-red-600 hover:text-red-800">&larr; Back to AI Tools</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Smart Risk Scoring Dashboard</h1>
        <p class="mt-1 text-sm text-gray-500">Employee security risk assessment based on training activity, quiz performance, and phishing simulation results.</p>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
        <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
            <p class="text-3xl font-bold text-gray-900">{{ $avgRiskScore }}</p>
            <p class="text-xs text-gray-500 mt-1">Avg Risk Score</p>
        </div>
        <div class="bg-white rounded-xl border border-green-200 p-4 text-center">
            <p class="text-3xl font-bold text-green-600">{{ $riskDistribution['low'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Low Risk</p>
        </div>
        <div class="bg-white rounded-xl border border-yellow-200 p-4 text-center">
            <p class="text-3xl font-bold text-yellow-600">{{ $riskDistribution['medium'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Medium Risk</p>
        </div>
        <div class="bg-white rounded-xl border border-orange-200 p-4 text-center">
            <p class="text-3xl font-bold text-orange-600">{{ $riskDistribution['high'] }}</p>
            <p class="text-xs text-gray-500 mt-1">High Risk</p>
        </div>
        <div class="bg-white rounded-xl border border-red-200 p-4 text-center">
            <p class="text-3xl font-bold text-red-600">{{ $riskDistribution['critical'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Critical Risk</p>
        </div>
    </div>

    {{-- Risk Distribution Bar --}}
    @php
        $total = array_sum($riskDistribution) ?: 1;
        $pLow = round(($riskDistribution['low'] / $total) * 100);
        $pMed = round(($riskDistribution['medium'] / $total) * 100);
        $pHigh = round(($riskDistribution['high'] / $total) * 100);
        $pCrit = round(($riskDistribution['critical'] / $total) * 100);
    @endphp
    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-8">
        <p class="text-sm font-medium text-gray-700 mb-2">Risk Distribution</p>
        <div class="flex rounded-full h-4 overflow-hidden bg-gray-100">
            @if($pLow > 0)<div class="bg-green-500" style="width:{{ $pLow }}%"></div>@endif
            @if($pMed > 0)<div class="bg-yellow-500" style="width:{{ $pMed }}%"></div>@endif
            @if($pHigh > 0)<div class="bg-orange-500" style="width:{{ $pHigh }}%"></div>@endif
            @if($pCrit > 0)<div class="bg-red-500" style="width:{{ $pCrit }}%"></div>@endif
        </div>
        <div class="flex justify-between text-xs text-gray-500 mt-1">
            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-green-500 inline-block"></span> Low {{ $pLow }}%</span>
            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-yellow-500 inline-block"></span> Medium {{ $pMed }}%</span>
            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-orange-500 inline-block"></span> High {{ $pHigh }}%</span>
            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-red-500 inline-block"></span> Critical {{ $pCrit }}%</span>
        </div>
    </div>

    {{-- User Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Employee Risk Scores</h2>
        </div>
        @if(count($riskData) === 0)
        <div class="p-8 text-center text-gray-500">
            <p>No employees found. Risk scoring requires employees with training activity.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Risk Score</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Level</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Courses</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Avg Quiz</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Key Factors</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($riskData as $entry)
                    <tr class="hover:bg-gray-50" x-data="{ open: false }">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <button @click="open = !open" class="text-left">
                                <div class="text-sm font-medium text-gray-900">{{ $entry['user']->name }}</div>
                                <div class="text-xs text-gray-500">{{ $entry['user']->email }}</div>
                            </button>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="text-lg font-bold {{ $entry['risk']['score'] >= 75 ? 'text-red-600' : ($entry['risk']['score'] >= 55 ? 'text-orange-600' : ($entry['risk']['score'] >= 35 ? 'text-yellow-600' : 'text-green-600')) }}">
                                {{ $entry['risk']['score'] }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @php
                                $levelColors = [
                                    'low' => 'bg-green-100 text-green-800',
                                    'medium' => 'bg-yellow-100 text-yellow-800',
                                    'high' => 'bg-orange-100 text-orange-800',
                                    'critical' => 'bg-red-100 text-red-800',
                                ];
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $levelColors[$entry['risk']['level']] ?? '' }}">
                                {{ ucfirst($entry['risk']['level']) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center text-sm text-gray-600">{{ $entry['stats']['course_completion_rate'] }}%</td>
                        <td class="px-6 py-4 text-center text-sm text-gray-600">{{ $entry['stats']['avg_quiz_score'] }}%</td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            @foreach(array_slice($entry['risk']['factors'], 0, 2) as $factor)
                                <span class="inline-flex items-center text-xs {{ $factor['impact'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                                    {{ $factor['impact'] > 0 ? '▲' : '▼' }} {{ $factor['factor'] }}
                                </span>
                                @if(!$loop->last)<br>@endif
                            @endforeach
                        </td>
                    </tr>
                    @if(!empty($entry['risk']['recommendations']))
                    <tr x-show="open" x-cloak class="bg-gray-50">
                        <td colspan="6" class="px-6 py-3">
                            <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Recommendations</p>
                            <ul class="list-disc pl-5 space-y-1">
                                @foreach($entry['risk']['recommendations'] as $rec)
                                <li class="text-sm text-gray-600">{{ $rec }}</li>
                                @endforeach
                            </ul>
                        </td>
                    </tr>
                    @endif
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endsection
