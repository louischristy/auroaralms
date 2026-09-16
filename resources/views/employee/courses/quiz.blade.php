@extends('layouts.app')
@section('title', 'Quiz: ' . $course->title . ' — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
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

    <form method="POST" action="{{ route('learn.courses.submit-quiz', $course) }}" id="quizForm">
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
@endsection
