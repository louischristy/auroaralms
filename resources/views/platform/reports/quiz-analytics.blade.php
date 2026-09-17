@extends('layouts.app')
@section('title', 'Quiz Analytics — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <div>
        <a href="{{ route('platform.reports.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 mb-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Reports
        </a>
        <h1 class="text-2xl font-bold text-gray-900">Quiz Analytics</h1>
    </div>

    <form method="GET" class="card p-4 flex items-end gap-4 flex-wrap">
        <div>
            <label class="label">From</label>
            <input type="date" name="from" value="{{ $from }}" class="input text-sm">
        </div>
        <div>
            <label class="label">To</label>
            <input type="date" name="to" value="{{ $to }}" class="input text-sm">
        </div>
        <div>
            <label class="label">Course</label>
            <select name="course_id" class="input text-sm">
                <option value="">All Courses</option>
                @foreach($coursesForFilter as $c)
                    <option value="{{ $c->id }}" {{ $courseId == $c->id ? 'selected' : '' }}>{{ $c->title }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn-primary text-sm">Filter</button>
    </form>

    {{-- Summary cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="card p-4 text-center">
            <p class="text-2xl font-bold text-gray-900">{{ $overallStats->total_attempts ?? 0 }}</p>
            <p class="text-xs text-gray-500 mt-1">Total Attempts</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-2xl font-bold text-green-600">{{ round($overallStats->avg_score ?? 0, 1) }}%</p>
            <p class="text-xs text-gray-500 mt-1">Average Score</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-2xl font-bold text-blue-600">
                {{ ($overallStats->total_attempts ?? 0) > 0 ? round(($overallStats->total_passed / $overallStats->total_attempts) * 100, 1) : 0 }}%
            </p>
            <p class="text-xs text-gray-500 mt-1">Pass Rate</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-2xl font-bold text-gray-700">{{ round(($overallStats->avg_time ?? 0) / 60, 1) }} min</p>
            <p class="text-xs text-gray-500 mt-1">Avg Time</p>
        </div>
    </div>

    {{-- Score distribution chart --}}
    <div class="card p-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">Score Distribution</h2>
        <canvas id="scoreChart" height="60"></canvas>
    </div>

    {{-- Hardest questions --}}
    <div class="card overflow-hidden">
        <div class="px-6 py-4 border-b bg-gray-50">
            <h2 class="text-sm font-semibold text-gray-700">Hardest Questions (min. 5 attempts)</h2>
        </div>
        <table class="w-full text-sm">
            <thead class="border-b">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Question</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Attempts</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Correct %</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($hardestQuestions as $q)
                <tr>
                    <td class="px-6 py-3 text-gray-900">{{ Str::limit($q->question, 80) }}</td>
                    <td class="px-6 py-3 text-gray-500 text-xs">{{ $q->course_title }}</td>
                    <td class="px-6 py-3 text-center">{{ $q->total_answers }}</td>
                    <td class="px-6 py-3 text-center">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                            {{ $q->correct_rate < 40 ? 'bg-red-100 text-red-700' : ($q->correct_rate < 70 ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700') }}">
                            {{ $q->correct_rate }}%
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="px-6 py-8 text-center text-gray-500">Not enough quiz data yet (need at least 5 attempts per question).</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('scoreChart'), {
    type: 'bar',
    data: {
        labels: @json(array_keys($buckets)),
        datasets: [{
            label: 'Attempts',
            data: @json(array_values($buckets)),
            backgroundColor: [
                'rgba(239, 68, 68, 0.7)',
                'rgba(249, 115, 22, 0.7)',
                'rgba(234, 179, 8, 0.7)',
                'rgba(59, 130, 246, 0.7)',
                'rgba(16, 185, 129, 0.7)',
            ],
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
    }
});
</script>
@endsection
