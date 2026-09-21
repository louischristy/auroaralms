@extends('layouts.app')
@section('title', 'Quiz: ' . $course->title . ' — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="max-w-3xl mx-auto space-y-6" x-data="{ showQuiz: {{ $pastAttempts->isEmpty() ? 'true' : 'false' }} }">
    <a href="{{ route('learn.courses.show', $course) }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to {{ $course->title }}
    </a>

    <div class="card p-6">
        <h1 class="text-xl font-bold text-gray-900 mb-1">{{ $quiz->title }}</h1>
        @if($quiz->instructions)
            <p class="text-sm text-gray-600 mb-4">{{ $quiz->instructions }}</p>
        @endif
        <div class="flex items-center gap-4 text-sm text-gray-400 mb-2">
            <span>{{ $questions->count() }} questions</span>
            <span>Pass: {{ $course->passing_score }}%</span>
            @if($quiz->time_limit_minutes)
                <span>Time limit: {{ $quiz->time_limit_minutes }} min</span>
            @endif
            @if($remaining !== null)
                <span>{{ $remaining }} attempt{{ $remaining !== 1 ? 's' : '' }} remaining</span>
            @endif
        </div>
        @if($alreadyPassed)
            <div class="p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">
                You have already passed this quiz. Retaking is optional.
            </div>
        @endif
    </div>

    {{-- Past attempt history --}}
    @if($pastAttempts->isNotEmpty())
    <div class="card overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Your Past Attempts</h2>
        </div>
        <div class="divide-y divide-gray-100">
            @foreach($pastAttempts as $attempt)
                <a href="{{ route('learn.courses.quiz-result', [$course, $attempt]) }}"
                   class="flex items-center justify-between px-6 py-3 hover:bg-gray-50 transition text-sm">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center {{ $attempt->passed ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-500' }}">
                            @if($attempt->passed)
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            @else
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                            @endif
                        </div>
                        <div>
                            <span class="text-gray-600">Attempt #{{ $loop->remaining + 1 }}</span>
                            <span class="text-gray-400 mx-1">&middot;</span>
                            <span class="text-gray-400">{{ $attempt->created_at->format('M d, Y \a\t H:i') }}</span>
                        </div>
                    </div>
                    <span class="font-semibold {{ $attempt->passed ? 'text-green-600' : 'text-red-500' }}">
                        {{ $attempt->score }}% ({{ $attempt->correct_count }}/{{ $attempt->total_questions }})
                        &mdash; {{ $attempt->passed ? 'Passed' : 'Failed' }}
                    </span>
                </a>
            @endforeach
        </div>
    </div>

    {{-- Retake button --}}
    <div class="card p-6 text-center" x-show="!showQuiz" x-cloak>
        <p class="text-sm text-gray-600 mb-4">
            @if($alreadyPassed)
                You have already passed. You can retake the quiz to improve your score.
            @else
                Ready to try again? Click below to start a new attempt.
            @endif
        </p>
        <button x-on:click="showQuiz = true; $nextTick(() => document.getElementById('quizForm').scrollIntoView({behavior: 'smooth'}))"
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            Retake Quiz
        </button>
    </div>
    @endif

    {{-- Quiz form --}}
    <div x-show="showQuiz" x-cloak>
        <form method="POST" action="{{ route('learn.courses.submit-quiz', $course) }}" id="quizForm" class="space-y-6">
            @csrf

            @foreach($questions as $qIndex => $question)
                <div class="card p-6">
                    <div class="flex items-start gap-3 mb-4">
                        <span class="flex-shrink-0 w-7 h-7 rounded-full flex items-center justify-center text-sm font-medium text-white" style="background: var(--color-primary)">
                            {{ $qIndex + 1 }}
                        </span>
                        <div class="flex-1">
                            <p class="font-medium text-gray-900">{{ $question->question }}</p>
                            @if($question->question_type === 'multi_select')
                                <p class="text-xs text-gray-400 mt-1">Select all that apply</p>
                            @endif
                        </div>
                    </div>

                    <div class="space-y-2 ml-10">
                        @foreach($question->answers as $answer)
                            <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:bg-gray-50 cursor-pointer transition-colors">
                                @if($question->question_type === 'multi_select')
                                    <input type="checkbox" name="question_{{ $question->id }}[]" value="{{ $answer->id }}"
                                           class="rounded border-gray-300 text-primary focus:ring-primary">
                                @else
                                    <input type="radio" name="question_{{ $question->id }}" value="{{ $answer->id }}"
                                           class="border-gray-300 text-primary focus:ring-primary">
                                @endif
                                <span class="text-sm text-gray-700">{{ $answer->answer_text }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div class="flex justify-end">
                <button type="submit" class="btn-primary"
                        onclick="return confirm('Are you sure you want to submit your quiz? You cannot change your answers after submission.')">
                    Submit Quiz
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
