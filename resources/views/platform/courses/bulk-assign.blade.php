@extends('layouts.app')
@section('title', 'Bulk Course Assignment — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="space-y-6" id="bulk-assign-page"
     data-courses='@json($courses->map(fn($c) => ["id" => $c->id, "category" => $c->category, "mandatory" => $c->is_mandatory]))'
     data-tenants='@json($tenants->map(fn($t) => ["id" => $t->id, "name" => $t->name]))'
     data-assignments='@json($assignments)'>

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

    {{-- Quick Assign by Category --}}
    <div class="card p-4">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Quick Assign by Category</h3>
        <div class="flex flex-wrap gap-2 items-center">
            <select id="qa-category" class="rounded-md border-gray-300 text-sm py-1.5 pr-8">
                <option value="">Select category…</option>
                @foreach($categories as $cat)
                <option value="{{ $cat }}">{{ $cat }}</option>
                @endforeach
                <option value="__mandatory__">★ All Mandatory Courses</option>
            </select>
            <span class="text-gray-400 text-sm">→</span>
            <select id="qa-tenant" class="rounded-md border-gray-300 text-sm py-1.5 pr-8">
                <option value="">Select tenant…</option>
                <option value="__all__">All Tenants</option>
                @foreach($tenants as $tenant)
                <option value="{{ $tenant->id }}">{{ $tenant->name }}</option>
                @endforeach
            </select>
            <button type="button" id="qa-assign-btn" class="btn-primary text-sm py-1.5 px-3">Assign</button>
            <button type="button" id="qa-unassign-btn" class="btn-outline text-sm py-1.5 px-3 text-red-600 border-red-300 hover:bg-red-50">Unassign</button>
        </div>
    </div>

    <form method="POST" action="{{ route('platform.courses.bulk-assign.save') }}">
        @csrf

        <div class="card overflow-hidden">
            <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <span class="text-sm font-medium text-gray-700">{{ $courses->count() }} courses &times; {{ $tenants->count() }} tenants</span>
                    <span id="change-counter" class="text-xs text-blue-600 font-medium hidden"></span>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" id="select-all-global" class="btn-outline text-xs py-1 px-2">Select All</button>
                    <button type="button" id="deselect-all-global" class="btn-outline text-xs py-1 px-2">Deselect All</button>
                    <button type="submit" class="btn-primary text-sm">Save All Assignments</button>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm" id="assign-matrix">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-left px-4 py-3 font-medium text-gray-600 sticky left-0 bg-gray-50 min-w-[200px] z-10">
                                Course
                            </th>
                            @foreach($tenants as $tenant)
                            <th class="text-center px-3 py-3 font-medium text-gray-600 min-w-[120px]">
                                <div class="text-xs">{{ $tenant->name }}</div>
                                <div class="text-[10px] text-gray-400 font-normal mb-1">{{ $tenant->subscription_plan }}</div>
                                <label class="inline-flex items-center gap-1 cursor-pointer">
                                    <input type="checkbox" class="col-toggle rounded border-gray-300 text-blue-600 focus:ring-blue-500" data-tenant-id="{{ $tenant->id }}">
                                    <span class="text-[10px] text-gray-400 font-normal">all</span>
                                </label>
                            </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($courses as $course)
                        <tr class="hover:bg-gray-50" data-course-id="{{ $course->id }}" data-category="{{ $course->category }}" data-mandatory="{{ $course->is_mandatory ? '1' : '0' }}">
                            <td class="px-4 py-3 font-medium text-gray-900 sticky left-0 bg-white z-10">
                                <div class="flex items-center gap-2">
                                    <label class="inline-flex items-center cursor-pointer shrink-0">
                                        <input type="checkbox" class="row-toggle rounded border-gray-300 text-blue-600 focus:ring-blue-500" data-course-id="{{ $course->id }}">
                                    </label>
                                    <div>
                                        <div>{{ $course->title }}@if($course->is_mandatory) <span class="text-amber-500 text-xs">★ mandatory</span>@endif</div>
                                        <div class="text-xs text-gray-400">{{ $course->category }} &middot; {{ $course->difficulty }}</div>
                                    </div>
                                </div>
                            </td>
                            @foreach($tenants as $tenant)
                            <td class="text-center px-3 py-3">
                                <input type="checkbox"
                                       name="assignments[{{ $course->id }}][]"
                                       value="{{ $tenant->id }}"
                                       {{ in_array($tenant->id, $assignments[$course->id] ?? []) ? 'checked' : '' }}
                                       class="cell-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                       data-course-id="{{ $course->id }}"
                                       data-tenant-id="{{ $tenant->id }}">
                            </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
                <span id="change-counter-bottom" class="text-xs text-blue-600 font-medium"></span>
                <button type="submit" class="btn-primary">Save All Assignments</button>
            </div>
        </div>
    </form>
    @endif
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/bulk-assign.js') }}" defer></script>
@endpush
