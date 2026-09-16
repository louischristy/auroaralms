<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizAnswer;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CourseManagementController extends Controller
{
    public function index(Request $request)
    {
        $courses = Course::withCount(['lessons', 'enrollments'])
            ->when($request->category, fn($q, $cat) => $q->where('category', $cat))
            ->when($request->search, fn($q, $s) => $q->where('title', 'like', "%{$s}%"))
            ->orderBy('category')
            ->orderBy('sort_order')
            ->paginate(20);

        $categories = Course::distinct()->pluck('category')->filter()->sort()->values();

        return view('platform.courses.index', compact('courses', 'categories'));
    }

    public function create()
    {
        $categories = $this->getCategories();
        return view('platform.courses.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'objectives' => 'nullable|string', // newline-separated
            'category' => 'required|string|max:100',
            'difficulty' => 'required|in:beginner,intermediate,advanced',
            'duration_minutes' => 'required|integer|min:1',
            'passing_score' => 'required|integer|min:0|max:100',
            'is_mandatory' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['title']);
        $validated['objectives'] = $this->parseObjectives($request->objectives);

        $course = Course::create($validated);

        return redirect()->route('platform.courses.edit', $course)
            ->with('success', 'Course created. Now add lessons and a quiz.');
    }

    public function edit(Course $course)
    {
        $course->load(['lessons' => fn($q) => $q->orderBy('sort_order'), 'quiz.questions.answers']);
        $categories = $this->getCategories();
        $tenants = Tenant::orderBy('name')->get();
        $assignedTenantIds = $course->tenants()->pluck('tenants.id')->toArray();

        return view('platform.courses.edit', compact('course', 'categories', 'tenants', 'assignedTenantIds'));
    }

    public function update(Request $request, Course $course)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'objectives' => 'nullable|string',
            'category' => 'required|string|max:100',
            'difficulty' => 'required|in:beginner,intermediate,advanced',
            'duration_minutes' => 'required|integer|min:1',
            'passing_score' => 'required|integer|min:0|max:100',
            'is_active' => 'boolean',
            'is_mandatory' => 'boolean',
        ]);

        $validated['objectives'] = $this->parseObjectives($request->objectives);
        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_mandatory'] = $request->boolean('is_mandatory');

        $course->update($validated);

        return back()->with('success', 'Course updated successfully.');
    }

    public function destroy(Course $course)
    {
        $course->delete();
        return redirect()->route('platform.courses.index')
            ->with('success', 'Course deleted.');
    }

    // ── Tenant assignment ──

    public function assignTenants(Request $request, Course $course)
    {
        $validated = $request->validate([
            'tenant_ids' => 'array',
            'tenant_ids.*' => 'exists:tenants,id',
        ]);

        $course->tenants()->sync($validated['tenant_ids'] ?? []);

        return back()->with('success', 'Tenant assignments updated.');
    }

    // ── Lesson management ──

    public function storeLesson(Request $request, Course $course)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'content_type' => 'required|in:text,video,interactive',
            'video_url' => 'nullable|url|max:500',
            'duration_minutes' => 'required|integer|min:1',
        ]);

        $validated['slug'] = Str::slug($validated['title']);
        $validated['sort_order'] = $course->lessons()->max('sort_order') + 1;

        $course->lessons()->create($validated);

        return back()->with('success', 'Lesson added.');
    }

    public function updateLesson(Request $request, Course $course, Lesson $lesson)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'content_type' => 'required|in:text,video,interactive',
            'video_url' => 'nullable|url|max:500',
            'duration_minutes' => 'required|integer|min:1',
        ]);

        $lesson->update($validated);

        return back()->with('success', 'Lesson updated.');
    }

    public function destroyLesson(Course $course, Lesson $lesson)
    {
        $lesson->delete();
        return back()->with('success', 'Lesson deleted.');
    }

    public function reorderLessons(Request $request, Course $course)
    {
        $validated = $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:lessons,id',
        ]);

        foreach ($validated['order'] as $index => $lessonId) {
            Lesson::where('id', $lessonId)->where('course_id', $course->id)
                ->update(['sort_order' => $index]);
        }

        return response()->json(['success' => true]);
    }

    // ── Quiz management ──

    public function storeQuiz(Request $request, Course $course)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'instructions' => 'nullable|string|max:2000',
            'time_limit_minutes' => 'nullable|integer|min:1',
            'max_attempts' => 'required|integer|min:0',
            'shuffle_questions' => 'boolean',
            'show_correct_answers' => 'boolean',
        ]);

        $validated['shuffle_questions'] = $request->boolean('shuffle_questions');
        $validated['show_correct_answers'] = $request->boolean('show_correct_answers');

        if ($course->quiz) {
            $course->quiz->update($validated);
        } else {
            $course->quiz()->create($validated);
        }

        return back()->with('success', 'Quiz saved.');
    }

    public function storeQuestion(Request $request, Course $course)
    {
        $quiz = $course->quiz;
        if (!$quiz) {
            return back()->with('error', 'Create a quiz first.');
        }

        $validated = $request->validate([
            'question' => 'required|string|max:2000',
            'question_type' => 'required|in:multiple_choice,true_false,multi_select',
            'explanation' => 'nullable|string|max:2000',
            'points' => 'required|integer|min:1',
            'answers' => 'required|array|min:2',
            'answers.*.text' => 'required|string|max:500',
            'answers.*.is_correct' => 'boolean',
        ]);

        $question = $quiz->questions()->create([
            'question' => $validated['question'],
            'question_type' => $validated['question_type'],
            'explanation' => $validated['explanation'],
            'points' => $validated['points'],
            'sort_order' => $quiz->questions()->max('sort_order') + 1,
        ]);

        foreach ($validated['answers'] as $index => $answerData) {
            $question->answers()->create([
                'answer_text' => $answerData['text'],
                'is_correct' => !empty($answerData['is_correct']),
                'sort_order' => $index,
            ]);
        }

        return back()->with('success', 'Question added.');
    }

    public function destroyQuestion(Course $course, QuizQuestion $question)
    {
        $question->delete();
        return back()->with('success', 'Question deleted.');
    }

    // ── Helpers ──

    private function getCategories(): array
    {
        return [
            'Phishing & Email Security',
            'Social Engineering',
            'Password & Authentication',
            'Data Protection & Privacy',
            'Malware & Ransomware',
            'Mobile & Remote Work Security',
            'Physical Security & Workplace Safety',
            'Incident Response & Compliance',
        ];
    }

    private function parseObjectives(?string $text): ?array
    {
        if (!$text) return null;
        return array_values(array_filter(array_map('trim', explode("\n", $text))));
    }
}
