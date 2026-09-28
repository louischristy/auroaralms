@extends('layouts.app')
@section('title', 'Survey Analytics — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Survey Analytics</h1>
        <div class="flex gap-3 text-sm">
            <a href="{{ route('manage.reports.export', 'surveys') }}" class="text-primary hover:underline">Export CSV</a>
            <a href="{{ route('manage.reports.index') }}" class="text-gray-500 hover:underline">{{ __('ui.back') }}</a>
        </div>
    </div>

    @forelse($surveys as $s)
        @php $avg = $s['avg_rating']; @endphp
        <div class="card p-6 space-y-4">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <h2 class="text-lg font-semibold text-gray-900">{{ $s['title'] }}</h2>
                <div class="text-right">
                    @if($avg !== null)
                        <span aria-label="{{ $avg }} out of 5" style="position:relative;display:inline-block;font-size:20px;letter-spacing:2px;color:#d1d5db">★★★★★<span style="position:absolute;left:0;top:0;overflow:hidden;white-space:nowrap;color:#f59e0b;width:{{ min(100, $avg / 5 * 100) }}%">★★★★★</span></span>
                        <div class="text-xs text-gray-500">{{ $avg }} / 5</div>
                    @else
                        <span class="text-sm text-gray-400">No ratings</span>
                    @endif
                </div>
            </div>

            <div>
                <div class="flex justify-between text-sm text-gray-600 mb-1">
                    <span>Response rate: {{ $s['responses'] }} of {{ $s['invited'] }} users</span>
                    <span>{{ $s['response_rate'] }}%</span>
                </div>
                <div style="height:8px;background:#e5e7eb;border-radius:9999px;overflow:hidden">
                    <div style="width:{{ min(100, $s['response_rate']) }}%;height:100%;background:#5BC0EB"></div>
                </div>
            </div>

            @if($s['questions']->count())
                <div class="space-y-3">
                    <h3 class="text-sm font-semibold text-gray-700">Question breakdown</h3>
                    @foreach($s['questions'] as $q)
                        <div class="text-sm border-t border-gray-100 pt-3">
                            <div class="flex justify-between gap-3">
                                <span class="text-gray-800">{{ $q['question'] }}</span>
                                <span class="text-gray-500 whitespace-nowrap">
                                    {{ $q['answers'] }} answers @if($q['avg'] !== null) · avg {{ $q['avg'] }} @endif
                                </span>
                            </div>
                            @if($q['distribution'])
                                @php $max = max($q['distribution']); @endphp
                                @foreach($q['distribution'] as $label => $n)
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="w-24 truncate text-xs text-gray-500">{{ $label }}</span>
                                        <div style="flex:1;height:6px;background:#e5e7eb;border-radius:9999px;overflow:hidden">
                                            <div style="width:{{ $max ? $n / $max * 100 : 0 }}%;height:100%;background:#2B4C7E"></div>
                                        </div>
                                        <span class="text-xs text-gray-500 w-6 text-right">{{ $n }}</span>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

            @if($s['trend'])
                <div class="text-xs text-gray-500 border-t border-gray-100 pt-3">
                    <span class="font-semibold text-gray-700">Trend:</span>
                    @foreach($s['trend'] as $month => $t)
                        <span class="inline-block mr-3">{{ $month }}: {{ $t['count'] }} responses @if($t['avg']) (avg {{ $t['avg'] }}) @endif</span>
                    @endforeach
                </div>
            @endif
        </div>
    @empty
        <div class="card p-8 text-center text-gray-500">{{ __('ui.no_results') }}</div>
    @endforelse
</div>
@endsection
