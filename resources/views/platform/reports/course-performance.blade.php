@extends('layouts.app')
@section('title', 'Course Performance — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('platform.reports.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 mb-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Reports
            </a>
            <h1 class="text-2xl font-bold text-gray-900">Course Performance</h1>
        </div>
        <a href="{{ route('platform.reports.course-performance', ['from' => $from, 'to' => $to, 'export' => 'csv']) }}"
           class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Export CSV
        </a>
    </div>

    <form method="GET" class="card p-4 flex items-end gap-4">
        <div>
            <label class="label">From</label>
            <input type="date" name="from" value="{{ $from }}" class="input text-sm">
        </div>
        <div>
            <label class="label">To</label>
            <input type="date" name="to" value="{{ $to }}" class="input text-sm">
        </div>
        <button type="submit" class="btn-primary text-sm">Filter</button>
    </form>

    {{-- Category chart --}}
    @if($categoryBreakdown->count() > 0)
    <div class="card p-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">Enrollment by Category</h2>
        <canvas id="categoryChart" height="80"></canvas>
    </div>
    @endif

    <div class="card overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Enrolled</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Completed</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Completion %</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Avg Score</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Pass Rate</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Avg Time</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($data as $row)
                <tr>
                    <td class="px-4 py-3 font-medium text-gray-900">{{ $row['title'] }}</td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $row['category'] }}</td>
                    <td class="px-4 py-3 text-center">{{ $row['enrolled'] }}</td>
                    <td class="px-4 py-3 text-center text-green-600">{{ $row['completed'] }}</td>
                    <td class="px-4 py-3 text-center">{{ $row['completion_rate'] }}%</td>
                    <td class="px-4 py-3 text-center font-medium {{ $row['avg_score'] >= 70 ? 'text-green-600' : ($row['avg_score'] > 0 ? 'text-yellow-600' : 'text-gray-400') }}">
                        {{ $row['avg_score'] > 0 ? $row['avg_score'] . '%' : '—' }}
                    </td>
                    <td class="px-4 py-3 text-center">{{ $row['quiz_attempts'] > 0 ? $row['pass_rate'] . '%' : '—' }}</td>
                    <td class="px-4 py-3 text-center text-gray-500">{{ $row['avg_time_min'] > 0 ? $row['avg_time_min'] . ' min' : '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-4 py-8 text-center text-gray-500">No course data for this period.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($categoryBreakdown->count() > 0)
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('categoryChart'), {
    type: 'bar',
    data: {
        labels: @json($categoryBreakdown->keys()),
        datasets: [
            {
                label: 'Enrolled',
                data: @json($categoryBreakdown->pluck('enrolled')->values()),
                backgroundColor: 'rgba(59, 130, 246, 0.7)',
            },
            {
                label: 'Completed',
                data: @json($categoryBreakdown->pluck('completed')->values()),
                backgroundColor: 'rgba(16, 185, 129, 0.7)',
            }
        ]
    },
    options: {
        responsive: true,
        scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
    }
});
</script>
@endif
@endsection
