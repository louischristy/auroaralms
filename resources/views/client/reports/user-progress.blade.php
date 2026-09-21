@extends('layouts.app')
@section('title', 'User Progress — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('manage.reports.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 mb-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Reports
            </a>
            <h1 class="text-2xl font-bold text-gray-900">User Progress</h1>
        </div>
        <a href="{{ route('manage.reports.user-progress', array_merge(request()->query(), ['export' => 'csv'])) }}"
           class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Export CSV
        </a>
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
            <label class="label">Department</label>
            <select name="department_id" class="input text-sm">
                <option value="">All Departments</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ $departmentId == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn-primary text-sm">Filter</button>
    </form>

    <div class="card overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Department</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Enrolled</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Completed</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Overdue</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Completion %</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Best Quiz Score</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Quiz Attempts</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($users as $user)
                <tr>
                    <td class="px-6 py-3">
                        <div class="font-medium text-gray-900">{{ $user->name }}</div>
                        <div class="text-xs text-gray-500">{{ $user->email }}</div>
                    </td>
                    <td class="px-6 py-3 text-gray-500">{{ $user->department?->name ?? '—' }}</td>
                    <td class="px-6 py-3 text-center">{{ $user->enrolled }}</td>
                    <td class="px-6 py-3 text-center text-green-600">{{ $user->completed }}</td>
                    <td class="px-6 py-3 text-center {{ $user->overdue > 0 ? 'text-red-600 font-medium' : 'text-gray-400' }}">{{ $user->overdue }}</td>
                    <td class="px-6 py-3 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <div class="w-16 bg-gray-200 rounded-full h-2">
                                <div class="h-2 rounded-full {{ $user->completion_rate >= 80 ? 'bg-green-500' : ($user->completion_rate >= 50 ? 'bg-yellow-500' : 'bg-red-500') }}"
                                     style="width: {{ min($user->completion_rate, 100) }}%"></div>
                            </div>
                            <span class="text-xs font-medium">{{ $user->completion_rate }}%</span>
                        </div>
                    </td>
                    <td class="px-6 py-3 text-center">
                        @if($user->quiz_best_score !== null)
                            <span class="font-medium {{ $user->quiz_best_score >= 80 ? 'text-green-600' : ($user->quiz_best_score >= 50 ? 'text-yellow-600' : 'text-red-500') }}">{{ $user->quiz_best_score }}%</span>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-6 py-3 text-center">
                        @if($user->quiz_attempts > 0)
                            <span class="text-gray-700">{{ $user->quiz_attempts }}</span>
                            <span class="text-xs text-gray-400">({{ $user->quiz_passed }} passed)</span>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-6 py-8 text-center text-gray-500">No users found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="flex justify-center">{{ $users->links() }}</div>
</div>
@endsection
