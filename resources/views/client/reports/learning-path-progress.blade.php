@extends('layouts.app')
@section('title', 'Learning Path Progress — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Learning Path Progress</h1>
        <div class="flex gap-3 text-sm">
            <a href="{{ route('manage.reports.export', 'learning-paths') }}" class="text-primary hover:underline">Export CSV</a>
            <a href="{{ route('manage.reports.index') }}" class="text-gray-500 hover:underline">{{ __('ui.back') }}</a>
        </div>
    </div>

    <div class="card overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-600">
                <tr>
                    <th class="px-4 py-3">Path</th>
                    <th class="px-4 py-3">Enrolled</th>
                    <th class="px-4 py-3">Completed</th>
                    <th class="px-4 py-3 w-56">Completion rate</th>
                    <th class="px-4 py-3">Avg days to complete</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($rows as $r)
                    <tr>
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $r['title'] }}</td>
                        <td class="px-4 py-3">{{ $r['enrolled'] }}</td>
                        <td class="px-4 py-3">{{ $r['completed'] }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <div style="flex:1;height:8px;background:#e5e7eb;border-radius:9999px;overflow:hidden">
                                    <div style="width:{{ $r['rate'] }}%;height:100%;background:#2B4C7E"></div>
                                </div>
                                <span class="w-12 text-right">{{ $r['rate'] }}%</span>
                            </div>
                        </td>
                        <td class="px-4 py-3">{{ $r['avg_days'] ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">{{ __('ui.no_results') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
