<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\ScormService;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ClientCourseController extends Controller
{
    private function tenantId(): ?int
    {
        return app()->bound('current_tenant_id') ? app('current_tenant_id') : null;
    }

    private function authorizeTenantCourse(Course $course): void
    {
        $tenantId = $this->tenantId();
        // Platform admin can access any tenant course
        if (!$tenantId) {
            return;
        }
        if ((int) $course->tenant_id !== $tenantId) {
            abort(Response::HTTP_FORBIDDEN, 'This course does not belong to your organization.');
        }
    }

    public function index()
    {
        $tenantId = $this->tenantId();

        // Platform admin sees all tenant-created courses across all tenants
        if (!$tenantId) {
            $courses = Course::whereNotNull('tenant_id')
                ->with('tenant:id,name')
                ->withCount('lessons')
                ->orderBy('created_at', 'desc')
                ->paginate(20);
            $platformCourseCount = Course::whereNull('tenant_id')->count();
        } else {
            $courses = Course::tenantCourses($tenantId)
                ->withCount('lessons')
                ->orderBy('created_at', 'desc')
                ->paginate(20);
            $platformCourseCount = DB::table('course_tenant')
                ->where('tenant_id', $tenantId)
                ->count();
        }

        return view('client.courses.index', compact('courses', 'platformCourseCount'));
    }

    public function create()
    {
        $categories = $this->getCategories();
        return view('client.courses.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'objectives' => 'nullable|string',
            'category' => 'required|string|max:100',
            'difficulty' => 'required|in:beginner,intermediate,advanced',
            'duration_minutes' => 'required|integer|min:1',
            'passing_score' => 'required|integer|min:0|max:100',
            'is_mandatory' => 'boolean',
        ]);

        $tenantId = $this->tenantId();

        if (!$tenantId) {
            return back()->with('error', 'Platform admins should use the Platform Course Manager to create courses. The Course Builder is for client admins to create tenant-specific courses.');
        }

        $validated['slug'] = Str::slug($validated['title']);
        $validated['objectives'] = $this->parseObjectives($request->objectives);
        $validated['tenant_id'] = $tenantId;

        $course = Course::create($validated);

        // Auto-assign to this tenant via pivot so it appears in employee views
        $course->tenants()->attach($tenantId);

        return redirect()->route('manage.courses.edit', $course)
            ->with('success', 'Course created. Now add lessons and a quiz.');
    }

    public function edit(Course $course)
    {
        $this->authorizeTenantCourse($course);

        $course->load(['lessons' => fn($q) => $q->orderBy('sort_order'), 'quiz.questions.answers']);
        $categories = $this->getCategories();

        return view('client.courses.edit', compact('course', 'categories'));
    }

    public function update(Request $request, Course $course)
    {
        $this->authorizeTenantCourse($course);

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
        $this->authorizeTenantCourse($course);

        $course->delete();

        return redirect()->route('manage.courses.index')
            ->with('success', 'Course deleted.');
    }

    // ── Lesson management ──

    public function storeLesson(Request $request, Course $course)
    {
        $this->authorizeTenantCourse($course);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'content_type' => 'required|in:text,video,interactive,scorm',
            'video_url' => 'nullable|url|max:500',
            'duration_minutes' => 'required|integer|min:1',
            'scorm_package' => 'nullable|file|mimes:zip|max:102400',
        ]);

        unset($validated['scorm_package']);
        $validated['slug'] = Str::slug($validated['title']);
        $validated['sort_order'] = $course->lessons()->max('sort_order') + 1;
        if ($validated['content_type'] !== 'scorm') {
            $validated['content'] = $validated['content'] ?? '';
        }

        $lesson = $course->lessons()->create($validated);

        if ($request->hasFile('scorm_package') && $validated['content_type'] === 'scorm') {
            $this->processScormUpload($request->file('scorm_package'), $course, $lesson);
        }

        return back()->with('success', 'Lesson added.');
    }

    public function updateLesson(Request $request, Course $course, Lesson $lesson)
    {
        $this->authorizeTenantCourse($course);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'content_type' => 'required|in:text,video,interactive,scorm',
            'video_url' => 'nullable|url|max:500',
            'duration_minutes' => 'required|integer|min:1',
            'scorm_package' => 'nullable|file|mimes:zip|max:102400',
        ]);

        unset($validated['scorm_package']);
        $lesson->update($validated);

        if ($request->hasFile('scorm_package') && $validated['content_type'] === 'scorm') {
            $this->processScormUpload($request->file('scorm_package'), $course, $lesson);
        }

        return back()->with('success', 'Lesson updated.');
    }

    private function processScormUpload($file, Course $course, $lesson): void
    {
        $scormService = new ScormService();
        $result = $scormService->extractPackage($file, $course->id, $lesson->id);

        $lesson->update([
            'scorm_version'      => $result['version'],
            'scorm_entry_point'  => $result['entry_point'],
            'scorm_package_path' => $result['package_path'],
            'content'            => '<p>This lesson uses a SCORM package. Launch the content above to begin.</p>',
        ]);
    }

    public function destroyLesson(Course $course, Lesson $lesson)
    {
        $this->authorizeTenantCourse($course);

        $lesson->delete();

        return back()->with('success', 'Lesson deleted.');
    }

    // ── Quiz management ──

    public function storeQuiz(Request $request, Course $course)
    {
        $this->authorizeTenantCourse($course);

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
        $this->authorizeTenantCourse($course);

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
        $this->authorizeTenantCourse($course);

        $question->delete();

        return back()->with('success', 'Question deleted.');
    }

    public function updateQuestion(Request $request, Course $course, QuizQuestion $question)
    {
        $this->authorizeTenantCourse($course);

        $validated = $request->validate([
            'question' => 'required|string|max:2000',
            'question_type' => 'required|in:multiple_choice,true_false,multi_select',
            'explanation' => 'nullable|string|max:2000',
            'points' => 'required|integer|min:1',
            'answers' => 'required|array|min:2',
            'answers.*.text' => 'required|string|max:500',
            'answers.*.is_correct' => 'boolean',
        ]);

        $question->update([
            'question' => $validated['question'],
            'question_type' => $validated['question_type'],
            'explanation' => $validated['explanation'],
            'points' => $validated['points'],
        ]);

        // Replace all answers
        $question->answers()->delete();
        foreach ($validated['answers'] as $index => $answerData) {
            $question->answers()->create([
                'answer_text' => $answerData['text'],
                'is_correct' => !empty($answerData['is_correct']),
                'sort_order' => $index,
            ]);
        }

        return back()->with('success', 'Question updated.');
    }

    public function reorderLessons(Request $request, Course $course)
    {
        $this->authorizeTenantCourse($course);

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

    public function preview(Course $course)
    {
        $this->authorizeTenantCourse($course);

        $course->load(['lessons' => fn($q) => $q->orderBy('sort_order'), 'quiz.questions.answers']);

        return view('client.courses.preview', compact('course'));
    }

    // ── Helpers ──

    private function getCategories(): array
    {
        return CourseCategory::active()->ordered()->pluck('name')->toArray();
    }

    private function parseObjectives(?string $text): ?array
    {
        if (!$text) return null;
        return array_values(array_filter(array_map('trim', explode("\n", $text))));
    }
}
