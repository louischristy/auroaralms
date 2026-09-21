@extends('layouts.app')
@section('title', 'Quiz Results — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <a href="{{ route('learn.courses.show', $course) }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to {{ $course->title }}
    </a>

    @if(session('badge'))
        <div class="bg-indigo-50 border border-indigo-200 text-indigo-700 rounded-lg px-4 py-3 text-sm flex items-center gap-2">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
            {{ session('badge') }}
        </div>
    @endif

    {{-- Score card --}}
    <div class="card p-8 text-center">
        <div class="inline-flex items-center justify-center w-24 h-24 rounded-full mb-4 {{ $attempt->passed ? 'bg-green-100' : 'bg-red-100' }}">
            @if($attempt->passed)
                <svg class="w-12 h-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            @else
                <svg class="w-12 h-12 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            @endif
        </div>
        <h1 class="text-2xl font-bold {{ $attempt->passed ? 'text-green-700' : 'text-red-700' }}">
            {{ $attempt->passed ? 'Congratulations! You Passed!' : 'Not Quite — Try Again' }}
        </h1>
        <p class="text-4xl font-bold text-gray-900 mt-2">{{ $attempt->score }}%</p>
        <p class="text-gray-500 mt-1">{{ $attempt->correct_count }} of {{ $attempt->total_questions }} correct</p>
        <p class="text-xs text-gray-400 mt-2">Passing score: {{ $course->passing_score }}%</p>

        @if(!$attempt->passed)
            <div class="mt-6">
                <a href="{{ route('learn.courses.quiz', $course) }}" class="btn-primary">Retry Quiz</a>
            </div>
        @endif
    </div>

    {{-- Question review --}}
    @if($attempt->quiz->show_correct_answers)
        <div class="card">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900">Review Your Answers</h2>
            </div>
            <div class="divide-y divide-gray-50">
                @foreach($attempt->responses as $response)
                    <div class="p-6">
                        <div class="flex items-start gap-3 mb-3">
                            <span class="flex-shrink-0 w-6 h-6 rounded-full flex items-center justify-center text-xs font-medium {{ $response->is_correct ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }}">
                                @if($response->is_correct)
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                @else
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                @endif
                            </span>
                            <p class="font-medium text-gray-900">{{ $response->question->question }}</p>
                        </div>
                        <div class="ml-9 space-y-1">
                            @foreach($response->question->answers as $answer)
                                @php
                                    $wasSelected = $response->answer_id === $answer->id ||
                                        (is_array($response->selected_answer_ids) && in_array($answer->id, $response->selected_answer_ids));
                                @endphp
                                <div class="flex items-center gap-2 text-sm px-3 py-1.5 rounded
                                    {{ $answer->is_correct ? 'bg-green-50 text-green-700' : ($wasSelected && !$answer->is_correct ? 'bg-red-50 text-red-700' : 'text-gray-500') }}">
                                    @if($answer->is_correct)
                                        <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                    @elseif($wasSelected)
                                        <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                    @else
                                        <span class="w-4 h-4 rounded-full border border-gray-300 inline-block"></span>
                                    @endif
                                    {{ $answer->answer_text }}
                                    @if($wasSelected && !$answer->is_correct)
                                        <span class="text-xs">(your answer)</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        @if($response->question->explanation)
                            <div class="ml-9 mt-2 p-3 bg-blue-50 rounded-lg text-sm text-blue-700">
                                <strong>Explanation:</strong> {{ $response->question->explanation }}
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
