<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\LessonCompletion;
use App\Models\QuizAttempt;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        return view('platform.reports.index');
    }

    /**
     * Completion rates across all tenants with date-range filtering.
     */
    public function tenantCompletion(Request $request)
    {
        $from = $request->input('from', now()->subMonths(3)->toDateString());
        $to = $request->input('to', now()->toDateString());

        $cacheKey = "report:tenant-completion:{$from}:{$to}";
        $data = Cache::remember($cacheKey, 600, function () use ($from, $to) {
            $tenants = Tenant::where('is_active', true)
                ->select('id', 'name')
                ->withCount('users')
                ->get();

            $enrollmentStats = CourseEnrollment::withoutTenantScope()
                ->whereBetween('created_at', [$from, "$to 23:59:59"])
                ->select(
                    'tenant_id',
                    DB::raw('COUNT(*) as enrolled'),
                    DB::raw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed"),
                    DB::raw("SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress"),
                    DB::raw("SUM(CASE WHEN due_date IS NOT NULL AND due_date < NOW() AND status != 'completed' THEN 1 ELSE 0 END) as overdue")
                )
                ->groupBy('tenant_id')
                ->get()
                ->keyBy('tenant_id');

            return $tenants->map(function ($tenant) use ($enrollmentStats) {
                $stats = $enrollmentStats->get($tenant->id);
                return [
                    'tenant_id' => $tenant->id,
                    'tenant' => $tenant->name,
                    'users' => $tenant->users_count,
                    'enrolled' => $stats?->enrolled ?? 0,
                    'completed' => $stats?->completed ?? 0,
                    'in_progress' => $stats?->in_progress ?? 0,
                    'overdue' => $stats?->overdue ?? 0,
                    'completion_rate' => ($stats?->enrolled ?? 0) > 0
                        ? round(($stats->completed / $stats->enrolled) * 100, 1) : 0,
                ];
            })->sortByDesc('completion_rate')->values();
        });

        if ($request->input('export') === 'csv') {
            return $this->exportCsv('tenant-completion', ['Tenant', 'Users', 'Enrolled', 'Completed', 'In Progress', 'Overdue', 'Completion %'], $data->toArray());
        }

        // Chart data for completion trend (monthly)
        $monthlyTrend = CourseEnrollment::withoutTenantScope()
            ->where('status', 'completed')
            ->whereBetween('completed_at', [$from, "$to 23:59:59"])
            ->select(
                DB::raw("DATE_FORMAT(completed_at, '%Y-%m') as month"),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month');

        return view('platform.reports.tenant-completion', compact('data', 'monthlyTrend', 'from', 'to'));
    }

    /**
     * Course performance — pass/fail rates, avg scores, time spent.
     */
    public function coursePerformance(Request $request)
    {
        $from = $request->input('from', now()->subMonths(3)->toDateString());
        $to = $request->input('to', now()->toDateString());

        $courses = Course::where('is_active', true)
            ->select('id', 'title', 'category', 'difficulty')
            ->withCount([
                'enrollments as total_enrolled' => fn($q) => $q->withoutTenantScope()->whereBetween('created_at', [$from, "$to 23:59:59"]),
                'enrollments as total_completed' => fn($q) => $q->withoutTenantScope()->where('status', 'completed')->whereBetween('created_at', [$from, "$to 23:59:59"]),
            ])
            ->get();

        // Quiz stats per course
        $quizStats = QuizAttempt::whereBetween('created_at', [$from, "$to 23:59:59"])
            ->select(
                'course_id',
                DB::raw('COUNT(*) as attempts'),
                DB::raw('AVG(score) as avg_score'),
                DB::raw("SUM(CASE WHEN passed = 1 THEN 1 ELSE 0 END) as passed"),
                DB::raw('AVG(time_spent_seconds) as avg_time')
            )
            ->groupBy('course_id')
            ->get()
            ->keyBy('course_id');

        $data = $courses->map(function ($course) use ($quizStats) {
            $quiz = $quizStats->get($course->id);
            return [
                'title' => $course->title,
                'category' => $course->category,
                'difficulty' => $course->difficulty,
                'enrolled' => $course->total_enrolled,
                'completed' => $course->total_completed,
                'completion_rate' => $course->total_enrolled > 0
                    ? round(($course->total_completed / $course->total_enrolled) * 100, 1) : 0,
                'quiz_attempts' => $quiz?->attempts ?? 0,
                'avg_score' => round($quiz?->avg_score ?? 0, 1),
                'pass_rate' => ($quiz?->attempts ?? 0) > 0
                    ? round(($quiz->passed / $quiz->attempts) * 100, 1) : 0,
                'avg_time_min' => round(($quiz?->avg_time ?? 0) / 60, 1),
            ];
        })->sortByDesc('enrolled')->values();

        if ($request->input('export') === 'csv') {
            return $this->exportCsv('course-performance', [
                'Course', 'Category', 'Difficulty', 'Enrolled', 'Completed', 'Completion %',
                'Quiz Attempts', 'Avg Score', 'Pass Rate %', 'Avg Time (min)'
            ], $data->toArray());
        }

        // Category breakdown for chart
        $categoryBreakdown = $data->groupBy('category')->map(function ($group) {
            return [
                'enrolled' => $group->sum('enrolled'),
                'completed' => $group->sum('completed'),
            ];
        });

        return view('platform.reports.course-performance', compact('data', 'categoryBreakdown', 'from', 'to'));
    }

    /**
     * Quiz analytics — question-level pass rates, hardest questions.
     */
    public function quizAnalytics(Request $request)
    {
        $from = $request->input('from', now()->subMonths(3)->toDateString());
        $to = $request->input('to', now()->toDateString());
        $courseId = $request->input('course_id');

        $coursesForFilter = Course::whereHas('quiz')
            ->select('id', 'title')
            ->orderBy('title')
            ->get();

        // Overall quiz stats
        $query = QuizAttempt::whereBetween('created_at', [$from, "$to 23:59:59"]);
        if ($courseId) {
            $query->where('course_id', $courseId);
        }

        $overallStats = (clone $query)->select(
            DB::raw('COUNT(*) as total_attempts'),
            DB::raw('AVG(score) as avg_score'),
            DB::raw("SUM(CASE WHEN passed = 1 THEN 1 ELSE 0 END) as total_passed"),
            DB::raw('AVG(time_spent_seconds) as avg_time'),
            DB::raw('MIN(score) as min_score'),
            DB::raw('MAX(score) as max_score')
        )->first();

        // Score distribution (buckets: 0-20, 21-40, 41-60, 61-80, 81-100)
        $scoreDistribution = (clone $query)->select(
            DB::raw("CASE
                WHEN score <= 20 THEN '0-20'
                WHEN score <= 40 THEN '21-40'
                WHEN score <= 60 THEN '41-60'
                WHEN score <= 80 THEN '61-80'
                ELSE '81-100'
            END as score_range"),
            DB::raw('COUNT(*) as count')
        )->groupBy('score_range')->pluck('count', 'score_range');

        // Ensure all buckets exist
        $buckets = ['0-20' => 0, '21-40' => 0, '41-60' => 0, '61-80' => 0, '81-100' => 0];
        foreach ($scoreDistribution as $range => $count) {
            $buckets[$range] = $count;
        }

        // Question-level difficulty (hardest questions)
        $questionDifficultyQuery = DB::table('quiz_responses')
            ->join('quiz_questions', 'quiz_responses.question_id', '=', 'quiz_questions.id')
            ->join('quizzes', 'quiz_questions.quiz_id', '=', 'quizzes.id')
            ->join('courses', 'quizzes.course_id', '=', 'courses.id')
            ->whereBetween('quiz_responses.created_at', [$from, "$to 23:59:59"])
            ->select(
                'quiz_questions.id',
                'quiz_questions.question',
                'courses.title as course_title',
                DB::raw('COUNT(*) as total_answers'),
                DB::raw("SUM(CASE WHEN quiz_responses.is_correct = 1 THEN 1 ELSE 0 END) as correct_answers")
            );

        if ($courseId) {
            $questionDifficultyQuery->where('courses.id', $courseId);
        }

        $hardestQuestions = $questionDifficultyQuery
            ->groupBy('quiz_questions.id', 'quiz_questions.question', 'courses.title')
            ->havingRaw('COUNT(*) >= 5')
            ->orderByRaw('SUM(CASE WHEN quiz_responses.is_correct = 1 THEN 1 ELSE 0 END) / COUNT(*) ASC')
            ->limit(15)
            ->get()
            ->map(function ($q) {
                $q->correct_rate = $q->total_answers > 0 ? round(($q->correct_answers / $q->total_answers) * 100, 1) : 0;
                return $q;
            });

        return view('platform.reports.quiz-analytics', compact(
            'overallStats', 'buckets', 'hardestQuestions', 'coursesForFilter', 'courseId', 'from', 'to'
        ));
    }

    /**
     * CSV export helper.
     */
    private function exportCsv(string $name, array $headers, array $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);
            foreach ($rows as $row) {
                fputcsv($handle, array_values($row));
            }
            fclose($handle);
        }, "{$name}-" . now()->format('Y-m-d') . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
}
