@extends('layouts.app')
@section('title', 'Bulk Course Assignment — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Bulk Course Assignment</h1>
            <p class="text-sm text-gray-500 mt-1">Assign platform courses to multiple tenants at once.</p>
        </div>
        <a href="{{ route('platform.courses.index') }}" class="btn-outline">Back to Courses</a>
    </div>

    @if($courses->isEmpty())
    <div class="card p-8 text-center text-gray-500">No active platform courses found.</div>
    @else
    <form method="POST" action="{{ route('platform.courses.bulk-assign.save') }}">
        @csrf

        <div class="card overflow-hidden">
            <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                <span class="text-sm font-medium text-gray-700">{{ $courses->count() }} courses &times; {{ $tenants->count() }} tenants</span>
                <button type="submit" class="btn-primary text-sm">Save All Assignments</button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-left px-4 py-3 font-medium text-gray-600 sticky left-0 bg-gray-50 min-w-[200px]">Course</th>
                            @foreach($tenants as $tenant)
                            <th class="text-center px-3 py-3 font-medium text-gray-600 min-w-[120px]">
                                <div class="text-xs">{{ $tenant->name }}</div>
                                <div class="text-[10px] text-gray-400 font-normal">{{ $tenant->subscription_plan }}</div>
                            </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($courses as $course)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900 sticky left-0 bg-white">
                                <div>{{ $course->title }}</div>
                                <div class="text-xs text-gray-400">{{ $course->category }} &middot; {{ $course->difficulty }}</div>
                            </td>
                            @foreach($tenants as $tenant)
                            <td class="text-center px-3 py-3">
                                <input type="checkbox"
                                       name="assignments[{{ $course->id }}][]"
                                       value="{{ $tenant->id }}"
                                       {{ in_array($tenant->id, $assignments[$course->id] ?? []) ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 bg-gray-50 border-t border-gray-200 text-right">
                <button type="submit" class="btn-primary">Save All Assignments</button>
            </div>
        </div>
    </form>
    @endif
</div>
@endsection
