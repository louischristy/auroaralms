@extends('layouts.app')
@section('title', 'Survey Results — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <a href="{{ route('manage.surveys.index') }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; Back to surveys</a>
            <h1 class="text-2xl font-bold text-gray-900 mt-2">{{ $survey->title }}</h1>
            <p class="text-sm text-gray-500 mt-1">
                {{ ucwords(str_replace('_', ' ', $survey->type)) }}
                @if($survey->course) &middot; {{ $survey->course->title }} @endif
                @if($survey->is_anonymous) &middot; Anonymous @endif
            </p>
        </div>
        <a href="{{ route('manage.surveys.edit', $survey->id) }}" class="btn-outline">Edit Survey</a>
    </div>

    {{-- Summary --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="card p-5">
            <p class="text-sm text-gray-500">Total Responses</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $totalResponses }}</p>
        </div>
        <div class="card p-5">
            <p class="text-sm text-gray-500">Average Rating</p>
            <div class="flex items-end gap-2 mt-1">
                <p class="text-3xl font-bold text-gray-900">{{ $overallAverage ?? '—' }}</p>
                @if($overallAverage)<span class="text-sm text-gray-400 pb-1">/ 5</span>@endif
            </div>
            @if($overallAverage)
                <div class="flex gap-0.5 mt-2">
                    @for($i = 1; $i <= 5; $i++)
                        <svg class="w-5 h-5 {{ $overallAverage >= $i - 0.25 ? 'text-amber-400' : 'text-gray-200' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    @endfor
                </div>
            @endif
        </div>
        <div class="card p-5">
            <p class="text-sm text-gray-500">Questions</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $survey->questions->count() }}</p>
        </div>
    </div>

    @if($totalResponses === 0)
        <div class="card p-10 text-center text-gray-400">No responses yet. Results will appear here once learners submit the survey.</div>
    @endif

    {{-- Per-question breakdown --}}
    @foreach($survey->questions as $n => $question)
        @php $st = $stats[$question->id]; $count = $st['count']; @endphp
        <div class="card p-6">
            <div class="flex items-start justify-between gap-4 mb-4">
                <div>
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">Question {{ $n + 1 }} &middot; {{ ucwords(str_replace('_', ' ', $question->type)) }}</p>
                    <h3 class="text-base font-semibold text-gray-900 mt-0.5">{{ $question->question }}</h3>
                </div>
                <div class="text-right flex-shrink-0">
                    <p class="text-sm text-gray-500">{{ $count }} {{ \Illuminate\Support\Str::plural('answer', $count) }}</p>
                    @if(in_array($question->type, ['rating', 'scale']) && $st['average'] !== null)
                        <p class="text-lg font-bold text-primary">{{ $st['average'] }} <span class="text-xs font-normal text-gray-400">/ {{ $st['max'] }}</span></p>
                    @endif
                </div>
            </div>

            @if($count === 0)
                <p class="text-sm text-gray-400">No answers yet.</p>

            @elseif(in_array($question->type, ['rating', 'scale']))
                <div class="space-y-2">
                    @foreach(array_reverse($st['distribution'], true) as $value => $num)
                        @php $pct = $count ? round($num / $count * 100) : 0; @endphp
                        <div class="flex items-center gap-3 text-sm">
                            <span class="w-6 text-right font-medium text-gray-600">{{ $value }}</span>
                            <div class="flex-1 h-5 bg-gray-100 rounded overflow-hidden">
                                <div class="h-full bg-primary rounded" style="width: {{ $pct }}%"></div>
                            </div>
                            <span class="w-20 text-xs text-gray-500">{{ $num }} ({{ $pct }}%)</span>
                        </div>
                    @endforeach
                </div>

            @elseif(in_array($question->type, ['yes_no', 'multiple_choice']))
                @php $total = max(array_sum($st['distribution']), 1); @endphp
                <div class="space-y-3">
                    @foreach($st['distribution'] as $label => $num)
                        @php $pct = round($num / $total * 100); @endphp
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-700">{{ $question->type === 'yes_no' ? ucfirst($label) : $label }}</span>
                                <span class="text-gray-500">{{ $num }} ({{ $pct }}%)</span>
                            </div>
                            <div class="h-3 bg-gray-100 rounded overflow-hidden">
                                <div class="h-full rounded {{ $question->type === 'yes_no' ? ($label === 'yes' ? 'bg-green-500' : 'bg-red-400') : 'bg-primary' }}" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>

            @else
                <ul class="divide-y divide-gray-100 max-h-80 overflow-y-auto rounded-lg border border-gray-100">
                    @foreach($st['answers'] as $answer)
                        <li class="px-4 py-3 text-sm text-gray-700 whitespace-pre-line">{{ $answer }}</li>
                    @endforeach
                </ul>
                @if($count > count($st['answers']))
                    <p class="text-xs text-gray-400 mt-2">Showing the latest {{ count($st['answers']) }} of {{ $count }} answers.</p>
                @endif
            @endif
        </div>
    @endforeach
</div>
@endsection
