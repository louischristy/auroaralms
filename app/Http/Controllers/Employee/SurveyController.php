<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\CourseEnrollment;
use App\Models\Survey;
use App\Models\SurveyResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SurveyController extends Controller
{
    public function index()
    {
        $surveys = $this->available()
            ->whereDoesntHave('responses', fn ($q) => $q->where('user_id', Auth::id()))
            ->with('course')
            ->withCount('questions')
            ->orderByDesc('is_required')
            ->latest()
            ->get();

        $completed = $this->available()
            ->whereHas('responses', fn ($q) => $q->where('user_id', Auth::id()))
            ->with('course')
            ->latest()
            ->limit(10)
            ->get();

        return view('employee.surveys.index', compact('surveys', 'completed'));
    }

    public function show($id)
    {
        $survey = $this->available()->with('questions')->findOrFail($id);

        if ($this->hasResponded($survey)) {
            return redirect()->route('learn.surveys.thank-you', $survey->id)
                ->with('info', 'You have already completed this survey.');
        }

        return view('employee.surveys.show', compact('survey'));
    }

    public function submit(Request $request, $id)
    {
        $survey = $this->available()->with('questions')->findOrFail($id);

        if ($this->hasResponded($survey)) {
            return redirect()->route('learn.surveys.thank-you', $survey->id)
                ->with('info', 'You have already completed this survey.');
        }

        // Build validation rules per question
        $rules = [];
        foreach ($survey->questions as $q) {
            $key = "answers.{$q->id}";
            $presence = $q->is_required ? 'required' : 'nullable';

            $rules[$key] = match ($q->type) {
                'rating' => [$presence, 'integer', 'between:1,5'],
                'scale' => [$presence, 'integer', 'between:1,10'],
                'yes_no' => [$presence, Rule::in(['yes', 'no'])],
                'multiple_choice' => [$presence, Rule::in($q->options ?? [])],
                default => [$presence, 'string', 'max:5000'],
            };
        }

        $validated = $request->validate($rules, [
            'answers.*.required' => 'Please answer all required questions.',
        ]);
        $given = $validated['answers'] ?? [];

        $answers = [];
        $ratings = [];
        foreach ($survey->questions as $q) {
            $value = $given[$q->id] ?? null;
            if ($value === null || $value === '') {
                continue;
            }
            if ($q->type === 'rating') {
                $ratings[] = (int) $value;
            }
            $answers[] = [
                'question_id' => $q->id,
                'value' => in_array($q->type, ['rating', 'scale'], true) ? (int) $value : $value,
            ];
        }

        SurveyResponse::create([
            'survey_id' => $survey->id,
            'tenant_id' => $survey->tenant_id,
            // user_id is kept so each person can respond once; for anonymous
            // surveys the results screens never expose it.
            'user_id' => Auth::id(),
            'answers' => $answers,
            'overall_rating' => $ratings ? (int) round(array_sum($ratings) / count($ratings)) : null,
            'submitted_at' => now(),
        ]);

        return redirect()->route('learn.surveys.thank-you', $survey->id);
    }

    public function thankYou($id)
    {
        $survey = $this->available()->findOrFail($id);

        return view('employee.surveys.thank-you', compact('survey'));
    }

    /** Active surveys for the user's tenant; course-linked ones only if enrolled. */
    private function available()
    {
        $enrolledCourseIds = CourseEnrollment::where('user_id', Auth::id())->pluck('course_id');

        return Survey::active()->where(function ($q) use ($enrolledCourseIds) {
            $q->whereNull('course_id')->orWhereIn('course_id', $enrolledCourseIds);
        });
    }

    private function hasResponded(Survey $survey): bool
    {
        return $survey->responses()->where('user_id', Auth::id())->exists();
    }
}
