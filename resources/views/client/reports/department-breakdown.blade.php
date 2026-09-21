@extends('layouts.app')
@section('title', 'Department Breakdown — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('manage.reports.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 mb-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Reports
            </a>
            <h1 class="text-2xl font-bold text-gray-900">Department Breakdown</h1>
        </div>
        <a href="{{ route('manage.reports.department-breakdown', ['from' => $from, 'to' => $to, 'export' => 'csv']) }}"
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

    {{-- Chart --}}
    @if($data->count() > 0)
    <div class="card p-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">Completion by Department</h2>
        <canvas id="deptChart" height="80"></canvas>
    </div>
    @endif

    <div class="card overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Department</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Users</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Enrolled</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Completed</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">In Progress</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Overdue</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Completion %</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($data as $row)
                <tr>
                    <td class="px-6 py-4 font-medium text-gray-900">{{ $row['department'] }}</td>
                    <td class="px-6 py-4 text-center text-gray-600">{{ $row['users'] }}</td>
                    <td class="px-6 py-4 text-center">{{ $row['enrolled'] }}</td>
                    <td class="px-6 py-4 text-center text-green-600">{{ $row['completed'] }}</td>
                    <td class="px-6 py-4 text-center text-blue-600">{{ $row['in_progress'] }}</td>
                    <td class="px-6 py-4 text-center {{ $row['overdue'] > 0 ? 'text-red-600 font-medium' : 'text-gray-400' }}">{{ $row['overdue'] }}</td>
                    <td class="px-6 py-4 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <div class="w-16 bg-gray-200 rounded-full h-2">
                                <div class="h-2 rounded-full {{ $row['completion_rate'] >= 80 ? 'bg-green-500' : ($row['completion_rate'] >= 50 ? 'bg-yellow-500' : 'bg-red-500') }}"
                                     style="width: {{ min($row['completion_rate'], 100) }}%"></div>
                            </div>
                            <span class="text-xs font-medium">{{ $row['completion_rate'] }}%</span>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-6 py-8 text-center text-gray-500">No department data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($data->count() > 0)
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<div id="deptChartData"
     data-labels="{{ e(json_encode($data->pluck('department')->values())) }}"
     data-values="{{ e(json_encode($data->pluck('completion_rate')->values())) }}"
     data-colors="{{ e(json_encode($data->map(fn($r) => $r['completion_rate'] >= 80 ? 'rgba(16, 185, 129, 0.7)' : ($r['completion_rate'] >= 50 ? 'rgba(234, 179, 8, 0.7)' : 'rgba(239, 68, 68, 0.7)'))->values())) }}"
     style="display:none"></div>
<script src="{{ asset('js/dept-chart.js') }}"></script>
@endif
@endsection
