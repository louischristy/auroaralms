@extends('layouts.app')
@section('title', $survey->title . ' — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <a href="{{ route('learn.surveys.index') }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; Back to surveys</a>

    <div class="card p-6">
        <div class="flex flex-wrap items-center gap-2 mb-2">
            <span class="badge-info">{{ ucwords(str_replace('_', ' ', $survey->type)) }}</span>
            @if($survey->is_anonymous)<span class="badge bg-gray-100 text-gray-600">Responses are anonymous</span>@endif
        </div>
        <h1 class="text-2xl font-bold text-gray-900">{{ $survey->title }}</h1>
        @if($survey->description)<p class="text-gray-600 mt-2">{{ $survey->description }}</p>@endif
        <p class="text-xs text-gray-400 mt-3">Questions marked <span class="text-red-500">*</span> are required.</p>
    </div>

    <form method="POST" action="{{ route('learn.surveys.submit', $survey->id) }}" class="space-y-4">
        @csrf

        @if($errors->any())
            <div class="p-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-700">
                Please answer all required questions and correct any highlighted answers.
            </div>
        @endif

        @foreach($survey->questions as $n => $q)
            @php $field = "answers.{$q->id}"; $name = "answers[{$q->id}]"; $old = old($field); @endphp
            <fieldset class="card p-6 {{ $errors->has($field) ? 'ring-2 ring-red-300' : '' }}">
                <legend class="sr-only">Question {{ $n + 1 }}</legend>
                <p class="text-sm font-semibold text-gray-900 mb-3">
                    <span class="text-gray-400 mr-1">{{ $n + 1 }}.</span>{{ $q->question }}
                    @if($q->is_required)<span class="text-red-500" title="Required">*</span>@endif
                </p>

                @if($q->type === 'rating')
                    <div x-data="{ value: {{ (int) $old }}, hover: 0 }" class="flex items-center gap-1">
                        <input type="hidden" name="{{ $name }}" :value="value || ''">
                        @for($i = 1; $i <= 5; $i++)
                            <button type="button" class="p-0.5 focus:outline-none focus:ring-2 focus:ring-primary rounded"
                                    @click="value = (value === {{ $i }} ? 0 : {{ $i }})" @mouseenter="hover = {{ $i }}" @mouseleave="hover = 0"
                                    aria-label="{{ $i }} {{ \Illuminate\Support\Str::plural('star', $i) }}">
                                <svg class="w-9 h-9 transition-colors" :class="(hover || value) >= {{ $i }} ? 'text-amber-400' : 'text-gray-300'" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            </button>
                        @endfor
                        <span class="ml-3 text-sm text-gray-500" x-show="value" x-text="value + ' / 5'"></span>
                    </div>

                @elseif($q->type === 'scale')
                    <div class="flex flex-wrap gap-2">
                        @for($i = 1; $i <= 10; $i++)
                            <label class="cursor-pointer">
                                <input type="radio" name="{{ $name }}" value="{{ $i }}" class="sr-only peer" {{ (string) $old === (string) $i ? 'checked' : '' }}>
                                <span class="w-10 h-10 flex items-center justify-center rounded-lg border border-gray-300 text-sm font-medium text-gray-600 hover:border-primary peer-checked:bg-primary peer-checked:text-white peer-checked:border-primary peer-focus-visible:ring-2 peer-focus-visible:ring-primary">{{ $i }}</span>
                            </label>
                        @endfor
                    </div>
                    <div class="flex justify-between text-xs text-gray-400 mt-2 max-w-[26rem]"><span>Not at all</span><span>Extremely</span></div>

                @elseif($q->type === 'yes_no')
                    <div class="flex gap-3">
                        @foreach(['yes' => 'Yes', 'no' => 'No'] as $val => $label)
                            <label class="cursor-pointer">
                                <input type="radio" name="{{ $name }}" value="{{ $val }}" class="sr-only peer" {{ $old === $val ? 'checked' : '' }}>
                                <span class="px-6 py-2 inline-block rounded-lg border border-gray-300 text-sm font-medium text-gray-600 hover:border-primary peer-checked:bg-primary peer-checked:text-white peer-checked:border-primary peer-focus-visible:ring-2 peer-focus-visible:ring-primary">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>

                @elseif($q->type === 'multiple_choice')
                    <div class="space-y-2">
                        @foreach($q->options ?? [] as $option)
                            <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50 has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                                <input type="radio" name="{{ $name }}" value="{{ $option }}" class="text-primary focus:ring-primary" {{ $old === $option ? 'checked' : '' }}>
                                <span class="text-sm text-gray-700">{{ $option }}</span>
                            </label>
                        @endforeach
                    </div>

                @else
                    <textarea name="{{ $name }}" rows="4" maxlength="5000" class="input" placeholder="Type your answer...">{{ $old }}</textarea>
                @endif

                @error($field)<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </fieldset>
        @endforeach

        <div class="flex items-center gap-3">
            <button type="submit" class="btn-primary">Submit Survey</button>
            <a href="{{ route('learn.surveys.index') }}" class="btn-outline">Cancel</a>
        </div>
    </form>
</div>
@endsection
