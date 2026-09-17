@extends('layouts.app')
@section('title', 'Overdue Training — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('manage.reports.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 mb-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Reports
            </a>
            <h1 class="text-2xl font-bold text-gray-900">Overdue Training</h1>
        </div>
        <a href="{{ route('manage.reports.overdue-training', ['export' => 'csv']) }}"
           class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Export CSV
        </a>
    </div>

    <div class="card overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Due Date</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Days Overdue</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Progress</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($overdue as $enrollment)
                <tr>
                    <td class="px-6 py-3">
                        <div class="font-medium text-gray-900">{{ $enrollment->user->name }}</div>
                        <div class="text-xs text-gray-500">{{ $enrollment->user->department?->name ?? '' }}</div>
                    </td>
                    <td class="px-6 py-3 text-gray-700">{{ $enrollment->course->title }}</td>
                    <td class="px-6 py-3 text-center text-gray-600">{{ $enrollment->due_date->format('M j, Y') }}</td>
                    <td class="px-6 py-3 text-center">
                        @php $days = now()->diffInDays($enrollment->due_date); @endphp
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                            {{ $days > 14 ? 'bg-red-100 text-red-700' : ($days > 7 ? 'bg-yellow-100 text-yellow-700' : 'bg-orange-100 text-orange-700') }}">
                            {{ $days }} days
                        </span>
                    </td>
                    <td class="px-6 py-3 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <div class="w-16 bg-gray-200 rounded-full h-2">
                                <div class="bg-red-500 h-2 rounded-full" style="width: {{ $enrollment->progress_percent }}%"></div>
                            </div>
                            <span class="text-xs">{{ $enrollment->progress_percent }}%</span>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-6 py-8 text-center text-gray-500">No overdue training. All users are on track!</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="flex justify-center">{{ $overdue->links() }}</div>
</div>
@endsection
