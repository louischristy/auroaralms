<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Concerns\ResolvesTenant;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SurveyController extends Controller
{
    use ResolvesTenant;

    public const TYPES = ['post_training', 'feedback', 'pulse', 'custom'];
    public const QUESTION_TYPES = ['rating', 'text', 'multiple_choice', 'yes_no', 'scale'];

    public function index(Request $request)
    {
        $surveys = Survey::with(['course', 'tenant'])
            ->withCount(['responses', 'questions'])
            ->when($request->search, fn ($q, $s) => $q->where('title', 'like', "%{$s}%"))
            ->when(in_array($request->type, self::TYPES, true), fn ($q) => $q->where('type', $request->type))
            ->when($request->status === 'active', fn ($q) => $q->where('is_active', true))
            ->when($request->status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('client.surveys.index', [
            'surveys' => $surveys,
            'types' => self::TYPES,
            'showTenant' => $this->isPlatformAdmin(),
        ]);
    }

    public function create()
    {
        return view('client.surveys.create', $this->formData());
    }

    public function store(Request $request)
    {
        [$data, $questions] = $this->validated($request);
        $tenantId = $this->resolveTenantId($request->integer('tenant_id') ?: null);
        $this->assertCourseAllowed($data['course_id'] ?? null, $tenantId);

        DB::transaction(function () use ($request, $data, $questions, $tenantId) {
            $survey = Survey::withoutTenantScope()->create($data + [
                'tenant_id' => $tenantId,
                'created_by' => Auth::id(),
                'is_anonymous' => $request->boolean('is_anonymous'),
                'is_required' => $request->boolean('is_required'),
                'is_active' => $request->boolean('is_active'),
            ]);

            foreach ($questions as $i => $q) {
                $survey->questions()->create($q + ['sort_order' => $i]);
            }
        });

        return redirect()->route('manage.surveys.index')->with('success', 'Survey created successfully.');
    }

    public function edit($id)
    {
        $survey = Survey::with('questions')->findOrFail($id);

        $initialQuestions = $survey->questions->map(fn (SurveyQuestion $q) => [
            'id' => $q->id,
            'question' => $q->question,
            'type' => $q->type,
            'options' => implode("\n", $q->options ?? []),
            'is_required' => (bool) $q->is_required,
        ])->values()->all();

        return view('client.surveys.edit', $this->formData($survey) + compact('survey', 'initialQuestions'));
    }

    public function update(Request $request, $id)
    {
        $survey = Survey::findOrFail($id);
        [$data, $questions] = $this->validated($request);
        $this->assertCourseAllowed($data['course_id'] ?? null, $survey->tenant_id);

        // Question ids must belong to this survey
        $existing = $survey->questions()->get()->keyBy('id');
        foreach ($questions as $q) {
            if (!empty($q['id']) && !$existing->has((int) $q['id'])) {
                throw ValidationException::withMessages(['questions' => 'Invalid question reference.']);
            }
        }

        DB::transaction(function () use ($request, $survey, $data, $questions, $existing) {
            $survey->update($data + [
                'is_anonymous' => $request->boolean('is_anonymous'),
                'is_required' => $request->boolean('is_required'),
                'is_active' => $request->boolean('is_active'),
            ]);

            $keep = [];
            foreach ($questions as $i => $q) {
                $id = $q['id'] ?? null;
                unset($q['id']);
                $q['sort_order'] = $i;

                if ($id) {
                    $existing[(int) $id]->update($q);
                    $keep[] = (int) $id;
                } else {
                    $keep[] = $survey->questions()->create($q)->id;
                }
            }

            $survey->questions()->whereNotIn('id', $keep)->delete();
        });

        return redirect()->route('manage.surveys.index')->with('success', 'Survey updated successfully.');
    }

    public function destroy($id)
    {
        Survey::findOrFail($id)->delete();

        return redirect()->route('manage.surveys.index')->with('success', 'Survey deleted.');
    }

    public function results($id)
    {
        $survey = Survey::with(['questions', 'course'])->findOrFail($id);
        $responses = $survey->responses()->with('user')->latest('submitted_at')->get();

        // Index answers by question id: [question_id => [values...]]
        $byQuestion = [];
        foreach ($responses as $response) {
            foreach ((array) $response->answers as $answer) {
                if (!isset($answer['question_id'])) {
                    continue;
                }
                $value = $answer['value'] ?? null;
                if ($value === null || $value === '' || $value === []) {
                    continue;
                }
                $byQuestion[$answer['question_id']][] = $value;
            }
        }

        $stats = [];
        foreach ($survey->questions as $question) {
            $values = $byQuestion[$question->id] ?? [];
            $stats[$question->id] = $this->summarise($question, $values);
        }

        $ratings = $responses->pluck('overall_rating')->filter();
        $overallAverage = $ratings->isNotEmpty() ? round($ratings->avg(), 2) : null;

        return view('client.surveys.results', [
            'survey' => $survey,
            'responses' => $responses,
            'stats' => $stats,
            'overallAverage' => $overallAverage,
            'totalResponses' => $responses->count(),
        ]);
    }

    private function summarise(SurveyQuestion $question, array $values): array
    {
        $count = count($values);
        $base = ['count' => $count, 'type' => $question->type];

        switch ($question->type) {
            case 'rating':
            case 'scale':
                $max = $question->type === 'rating' ? 5 : 10;
                $distribution = array_fill_keys(range(1, $max), 0);
                foreach ($values as $v) {
                    $v = (int) $v;
                    if (isset($distribution[$v])) {
                        $distribution[$v]++;
                    }
                }
                $numeric = array_map('intval', $values);

                return $base + [
                    'average' => $count ? round(array_sum($numeric) / $count, 2) : null,
                    'max' => $max,
                    'distribution' => $distribution,
                ];

            case 'yes_no':
                $distribution = ['yes' => 0, 'no' => 0];
                foreach ($values as $v) {
                    $key = strtolower((string) $v);
                    if (isset($distribution[$key])) {
                        $distribution[$key]++;
                    }
                }

                return $base + ['distribution' => $distribution];

            case 'multiple_choice':
                $distribution = array_fill_keys($question->options ?? [], 0);
                foreach ($values as $v) {
                    foreach ((array) $v as $choice) {
                        if (array_key_exists($choice, $distribution)) {
                            $distribution[$choice]++;
                        }
                    }
                }

                return $base + ['distribution' => $distribution];

            default: // text
                return $base + ['answers' => array_slice(array_map('strval', $values), 0, 100)];
        }
    }

    /** @return array{0: array, 1: array} survey data and normalised questions */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'type' => ['required', Rule::in(self::TYPES)],
            'course_id' => ['nullable', 'integer', 'exists:courses,id'],
            'tenant_id' => [$this->isPlatformAdmin() ? 'required' : 'nullable', 'integer', 'exists:tenants,id'],
            'questions' => ['required', 'array', 'min:1', 'max:50'],
            'questions.*.id' => ['nullable', 'integer'],
            'questions.*.question' => ['required', 'string', 'max:1000'],
            'questions.*.type' => ['required', Rule::in(self::QUESTION_TYPES)],
            'questions.*.options' => ['nullable', 'string', 'max:5000'],
            'questions.*.is_required' => ['nullable', 'boolean'],
        ], [
            'questions.required' => 'Add at least one question.',
            'questions.min' => 'Add at least one question.',
            'questions.*.question.required' => 'Every question needs some text.',
        ]);

        $questions = [];
        foreach (array_values($data['questions']) as $i => $q) {
            $options = null;
            if ($q['type'] === 'multiple_choice') {
                $options = collect(preg_split('/\r\n|\r|\n/', (string) ($q['options'] ?? '')))
                    ->map(fn ($o) => trim($o))->filter()->unique()->values()->all();

                if (count($options) < 2) {
                    throw ValidationException::withMessages([
                        "questions.$i.options" => 'Multiple choice questions need at least two options (one per line).',
                    ]);
                }
            }

            $questions[] = [
                'id' => $q['id'] ?? null,
                'question' => $q['question'],
                'type' => $q['type'],
                'options' => $options,
                'min_value' => match ($q['type']) { 'rating' => 1, 'scale' => 1, default => null },
                'max_value' => match ($q['type']) { 'rating' => 5, 'scale' => 10, default => null },
                'is_required' => (bool) ($q['is_required'] ?? false),
            ];
        }

        unset($data['questions'], $data['tenant_id']);

        return [$data, $questions];
    }

    /** A survey may only link to a platform-wide course or one owned by its tenant. */
    private function assertCourseAllowed(?int $courseId, ?int $tenantId): void
    {
        if (!$courseId) {
            return;
        }

        $ok = Course::where('id', $courseId)
            ->where(fn ($q) => $q->whereNull('tenant_id')->orWhere('tenant_id', $tenantId))
            ->exists();

        if (!$ok) {
            throw ValidationException::withMessages(['course_id' => 'The selected course is not available for this organization.']);
        }
    }

    private function formData(?Survey $survey = null): array
    {
        $tenantId = $survey?->tenant_id ?? Auth::user()->tenant_id;

        $courses = Course::when($tenantId, fn ($q) => $q->where(fn ($w) => $w->whereNull('tenant_id')->orWhere('tenant_id', $tenantId)))
            ->orderBy('title')
            ->get(['id', 'title']);

        return [
            'tenants' => $survey ? null : $this->tenantOptions(),
            'courses' => $courses,
            'types' => self::TYPES,
        ];
    }
}
