<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Lesson;
use App\Models\LessonCompletion;
use App\Models\QuizAttempt;
use App\Models\QuizResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CourseController extends Controller
{
    /**
     * List courses available/enrolled for the current user.
     */
    public function index()
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id;

        // Get courses assigned to user's tenant
        $courseIds = DB::table('course_tenant')
            ->where('tenant_id', $tenantId)
            ->pluck('course_id');

        $courses = Course::where(function ($q) use ($courseIds, $tenantId) {
                $q->whereIn('id', $courseIds)
                  ->orWhere('tenant_id', $tenantId);
            })
            ->where('is_active', true)
            ->select('id', 'title', 'slug', 'description', 'category', 'difficulty', 'thumbnail_path', 'duration_minutes', 'is_mandatory', 'sort_order')
            ->withCount(['lessons' => fn($q) => $q->where('is_active', true)])
            ->orderBy('category')
            ->orderBy('sort_order')
            ->get();

        // Get enrollments for this user
        $enrollments = CourseEnrollment::where('user_id', $user->id)
            ->select('id', 'user_id', 'course_id', 'status', 'progress_percent', 'due_date', 'completed_at')
            ->get()
            ->keyBy('course_id');

        return view('employee.courses.index', compact('courses', 'enrollments'));
    }

    /**
     * Show course overview with lessons list.
     */
    public function show(Course $course)
    {
        $user = Auth::user();

        // Verify tenant access (platform admins can view all courses)
        if ($user->tenant_id) {
            $hasAccess = ($course->tenant_id && $course->tenant_id === $user->tenant_id)
                || DB::table('course_tenant')
                    ->where('course_id', $course->id)
                    ->where('tenant_id', $user->tenant_id)
                    ->exists();

            if (!$hasAccess) {
                abort(403, 'You do not have access to this course.');
            }
        }

        $course->load([
            'lessons' => fn($q) => $q->where('is_active', true)->orderBy('sort_order'),
            'quiz',
        ]);

        // Auto-enroll if not enrolled (skip for platform admins with no tenant)
        $enrollment = null;
        if ($user->tenant_id) {
            $enrollment = CourseEnrollment::firstOrCreate(
                ['user_id' => $user->id, 'course_id' => $course->id],
                ['tenant_id' => $user->tenant_id, 'status' => 'not_started']
            );
        }

        // Get completed lesson IDs
        $completedLessonIds = LessonCompletion::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->pluck('lesson_id')
            ->toArray();

        // Get quiz attempts
        $quizAttempts = $course->quiz
            ? QuizAttempt::where('user_id', $user->id)
                ->where('quiz_id', $course->quiz->id)
                ->select('id', 'user_id', 'quiz_id', 'course_id', 'score', 'passed', 'completed_at', 'created_at')
                ->orderByDesc('created_at')
                ->get()
            : collect();

        $quizPassed = $quizAttempts->contains('passed', true);

        return view('employee.courses.show', compact(
            'course', 'enrollment', 'completedLessonIds', 'quizAttempts', 'quizPassed'
        ));
    }

    /**
     * Show a specific lesson.
     */
    public function lesson(Course $course, Lesson $lesson)
    {
        $user = Auth::user();

        if ($user->tenant_id) {
            $hasAccess = ($course->tenant_id && $course->tenant_id === $user->tenant_id)
                || DB::table('course_tenant')
                    ->where('course_id', $course->id)
                    ->where('tenant_id', $user->tenant_id)
                    ->exists();

            if (!$hasAccess) abort(403);
        }

        $course->load(['lessons' => fn($q) => $q->where('is_active', true)->orderBy('sort_order')]);

        $completedLessonIds = LessonCompletion::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->pluck('lesson_id')
            ->toArray();

        // Find prev/next lessons
        $lessons = $course->lessons;
        $currentIndex = $lessons->search(fn($l) => $l->id === $lesson->id);
        $prevLesson = $currentIndex > 0 ? $lessons[$currentIndex - 1] : null;
        $nextLesson = $currentIndex < $lessons->count() - 1 ? $lessons[$currentIndex + 1] : null;

        $isCompleted = in_array($lesson->id, $completedLessonIds);

        return view('employee.courses.lesson', compact(
            'course', 'lesson', 'lessons', 'completedLessonIds', 'prevLesson', 'nextLesson', 'isCompleted'
        ));
    }

    /**
     * Mark a lesson as completed.
     */
    public function completeLesson(Request $request, Course $course, Lesson $lesson)
    {
        $user = Auth::user();

        LessonCompletion::firstOrCreate(
            ['user_id' => $user->id, 'lesson_id' => $lesson->id, 'course_id' => $course->id],
            ['completed_at' => now(), 'time_spent_seconds' => 0]
        );

        // Recalculate progress
        $enrollment = CourseEnrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if ($enrollment) {
            $enrollment->recalculateProgress();
        }

        // Go to next lesson or back to course
        $nextLesson = Lesson::where('course_id', $course->id)
            ->where('is_active', true)
            ->where('sort_order', '>', $lesson->sort_order)
            ->orderBy('sort_order')
            ->first();

        if ($nextLesson) {
            return redirect()->route('learn.courses.lesson', [$course, $nextLesson])
                ->with('success', 'Lesson completed! Moving to next lesson.');
        }

        return redirect()->route('learn.courses.show', $course)
            ->with('success', 'Lesson completed! ' . ($course->quiz ? 'Take the quiz to finish the course.' : 'Course complete!'));
    }

    /**
     * Show quiz page.
     */
    public function showQuiz(Course $course)
    {
        $user = Auth::user();
        $quiz = $course->quiz;

        if (!$quiz || !$quiz->is_active) {
            return redirect()->route('learn.courses.show', $course)
                ->with('error', 'No quiz available for this course.');
        }

        // Check remaining attempts
        $remaining = $quiz->remainingAttempts($user->id);
        if ($remaining !== null && $remaining <= 0) {
            return redirect()->route('learn.courses.show', $course)
                ->with('error', 'You have used all quiz attempts.');
        }

        // Check if already passed
        $alreadyPassed = QuizAttempt::where('user_id', $user->id)
            ->where('quiz_id', $quiz->id)
            ->where('passed', true)
            ->exists();

        $questions = $quiz->questions()->with('answers')->get();
        if ($quiz->shuffle_questions) {
            $questions = $questions->shuffle();
        }

        return view('employee.courses.quiz', compact('course', 'quiz', 'questions', 'remaining', 'alreadyPassed'));
    }

    /**
     * Submit quiz answers and grade.
     */
    public function submitQuiz(Request $request, Course $course)
    {
        $user = Auth::user();
        $quiz = $course->quiz;

        if (!$quiz) abort(404);

        // Check remaining attempts
        $remaining = $quiz->remainingAttempts($user->id);
        if ($remaining !== null && $remaining <= 0) {
            return redirect()->route('learn.courses.show', $course)
                ->with('error', 'No attempts remaining.');
        }

        $questions = $quiz->questions()->with('answers')->get();

        // Create attempt
        $attempt = QuizAttempt::create([
            'user_id' => $user->id,
            'quiz_id' => $quiz->id,
            'course_id' => $course->id,
            'started_at' => now(),
            'total_questions' => $questions->count(),
        ]);

        $correctCount = 0;

        foreach ($questions as $question) {
            $inputKey = 'question_' . $question->id;

            if ($question->question_type === 'multi_select') {
                $selectedIds = $request->input($inputKey, []);
                $correctIds = $question->answers->where('is_correct', true)->pluck('id')->toArray();
                $selectedSorted = collect($selectedIds)->map(fn($v) => (int) $v)->sort()->values()->toArray();
                sort($correctIds);
                $isCorrect = $selectedSorted === $correctIds;

                QuizResponse::create([
                    'attempt_id' => $attempt->id,
                    'question_id' => $question->id,
                    'selected_answer_ids' => $selectedIds,
                    'is_correct' => $isCorrect,
                ]);
            } else {
                $answerId = $request->input($inputKey);
                $isCorrect = $answerId && $question->answers
                    ->where('id', (int) $answerId)
                    ->where('is_correct', true)
                    ->isNotEmpty();

                QuizResponse::create([
                    'attempt_id' => $attempt->id,
                    'question_id' => $question->id,
                    'answer_id' => $answerId,
                    'is_correct' => $isCorrect,
                ]);
            }

            if ($isCorrect) $correctCount++;
        }

        // Calculate score
        $score = $questions->count() > 0
            ? (int) round(($correctCount / $questions->count()) * 100)
            : 0;

        $passed = $score >= $course->passing_score;

        $attempt->update([
            'score' => $score,
            'correct_count' => $correctCount,
            'passed' => $passed,
            'completed_at' => now(),
        ]);

        // Recalculate course progress
        $enrollment = CourseEnrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if ($enrollment) {
            $enrollment->recalculateProgress();
        }

        return redirect()->route('learn.courses.quiz-result', [$course, $attempt]);
    }

    /**
     * Show quiz results.
     */
    public function quizResult(Course $course, QuizAttempt $attempt)
    {
        $user = Auth::user();
        if ($attempt->user_id !== $user->id) abort(403);

        $attempt->load(['responses.question.answers', 'responses.answer', 'quiz']);

        return view('employee.courses.quiz-result', compact('course', 'attempt'));
    }
}
