@extends('layouts.app')
@section('title', $path->title . ' — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                @if($path->icon) <span class="mr-2">{{ $path->icon }}</span> @endif
                {{ $path->title }}
            </h1>
            <p class="text-sm text-gray-500 mt-1">{{ $path->description }}</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $path->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                {{ $path->is_active ? 'Active' : 'Inactive' }}
            </span>
            <a href="{{ route('manage.career-paths.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Back</a>
        </div>
    </div>

    {{-- Stages --}}
    <div class="card p-6 space-y-4">
        <h2 class="text-lg font-semibold text-gray-900">Stages ({{ $path->stages->count() }})</h2>

        @forelse($path->stages as $stage)
            <div class="border border-gray-200 rounded-lg p-4">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="font-medium text-gray-900">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 text-xs font-bold mr-2">{{ $stage->stage_order + 1 }}</span>
                            {{ $stage->title }}
                        </h3>
                        @if($stage->description)
                            <p class="text-sm text-gray-500 mt-1">{{ $stage->description }}</p>
                        @endif
                    </div>
                    @if($stage->estimated_duration_days)
                        <span class="text-xs text-gray-400">~{{ $stage->estimated_duration_days }} days</span>
                    @endif
                </div>

                <div class="mt-3 flex flex-wrap gap-4 text-sm">
                    @if(!empty($stage->required_courses))
                        <div>
                            <span class="text-gray-500 font-medium">Courses:</span>
                            @foreach($stage->required_courses as $cid)
                                <span class="inline-flex px-2 py-0.5 rounded bg-blue-50 text-blue-700 text-xs ml-1">{{ $courses[$cid]->title ?? "Course #$cid" }}</span>
                            @endforeach
                        </div>
                    @endif
                    @if(!empty($stage->required_certifications))
                        <div>
                            <span class="text-gray-500 font-medium">Certifications:</span>
                            @foreach($stage->required_certifications as $cert)
                                <span class="inline-flex px-2 py-0.5 rounded bg-violet-50 text-violet-700 text-xs ml-1">{{ $cert }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <p class="text-gray-400 text-center py-4">No stages defined for this career path.</p>
        @endforelse
    </div>

    {{-- Enrolled Users --}}
    <div class="card p-6 space-y-4">
        <h2 class="text-lg font-semibold text-gray-900">Enrolled Users ({{ $path->enrollments->count() }})</h2>

        @if($path->enrollments->isNotEmpty())
            <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Stage</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Enrolled</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Completed</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @foreach($path->enrollments as $enrollment)
                        <tr>
                            <td class="px-6 py-3 text-sm text-gray-900">{{ $enrollment->user->name ?? 'Unknown' }}</td>
                            <td class="px-6 py-3 text-sm text-gray-600">{{ $enrollment->currentStage->title ?? 'Not started' }}</td>
                            <td class="px-6 py-3 text-sm text-gray-500">{{ $enrollment->enrolled_at?->format('M j, Y') ?? '-' }}</td>
                            <td class="px-6 py-3 text-sm">
                                @if($enrollment->completed_at)
                                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700">{{ $enrollment->completed_at->format('M j, Y') }}</span>
                                @else
                                    <span class="text-gray-400">In progress</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        @else
            <p class="text-gray-400 text-center py-4">No users enrolled yet.</p>
        @endif
    </div>
</div>
@endsection
