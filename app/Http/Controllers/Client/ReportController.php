<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Department;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index()
    {
        return view('client.reports.index');
    }

    /**
     * User progress — per-employee completion breakdown.
     */
    public function userProgress(Request $request)
    {
        $from = $request->input('from', now()->subMonths(3)->toDateString());
        $to = $request->input('to', now()->toDateString());
        $departmentId = $request->input('department_id');

        $departments = Department::orderBy('name')->get(['id', 'name']);

        $query = User::select('id', 'name', 'email', 'department_id')
            ->with('department:id,name')
            ->withCount([
                'courseEnrollments as enrolled' => fn($q) => $q->whereBetween('created_at', [$from, "$to 23:59:59"]),
                'courseEnrollments as completed' => fn($q) => $q->where('status', 'completed')->whereBetween('created_at', [$from, "$to 23:59:59"]),
                'courseEnrollments as overdue' => fn($q) => $q->whereNotNull('due_date')
                    ->where('due_date', '<', now())
                    ->where('status', '!=', 'completed')
                    ->whereBetween('created_at', [$from, "$to 23:59:59"]),
            ]);

        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        $users = $query->orderBy('name')->paginate(30)->appends($request->query());

        // Get quiz attempt stats per user
        $quizStats = DB::table('quiz_attempts')
            ->join('courses', 'courses.id', '=', 'quiz_attempts.course_id')
            ->whereIn('quiz_attempts.user_id', $users->pluck('id'))
            ->whereNotNull('quiz_attempts.completed_at')
            ->select(
                'quiz_attempts.user_id',
                DB::raw('COUNT(*) as total_attempts'),
                DB::raw('MAX(quiz_attempts.score) as best_score'),
                DB::raw("SUM(CASE WHEN quiz_attempts.passed = 1 THEN 1 ELSE 0 END) as passed_count")
            )
            ->groupBy('quiz_attempts.user_id')
            ->get()
            ->keyBy('user_id');

        $users->getCollection()->transform(function ($user) use ($quizStats) {
            $user->completion_rate = $user->enrolled > 0
                ? round(($user->completed / $user->enrolled) * 100, 1) : 0;
            $stats = $quizStats->get($user->id);
            $user->quiz_best_score = $stats?->best_score ?? null;
            $user->quiz_attempts = $stats?->total_attempts ?? 0;
            $user->quiz_passed = $stats?->passed_count ?? 0;
            return $user;
        });

        if ($request->input('export') === 'csv') {
            // Get all for export (no pagination)
            $allUsers = (clone $query)->get()->map(function ($user) {
                return [
                    'name' => $user->name,
                    'email' => $user->email,
                    'department' => $user->department?->name ?? '—',
                    'enrolled' => $user->enrolled,
                    'completed' => $user->completed,
                    'overdue' => $user->overdue,
                    'completion_rate' => $user->enrolled > 0
                        ? round(($user->completed / $user->enrolled) * 100, 1) : 0,
                ];
            });

            return $this->exportCsv('user-progress', [
                'Name', 'Email', 'Department', 'Enrolled', 'Completed', 'Overdue', 'Completion %'
            ], $allUsers->toArray());
        }

        return view('client.reports.user-progress', compact('users', 'departments', 'departmentId', 'from', 'to'));
    }

    /**
     * Department breakdown — completion by department with drill-down.
     */
    public function departmentBreakdown(Request $request)
    {
        $from = $request->input('from', now()->subMonths(3)->toDateString());
        $to = $request->input('to', now()->toDateString());

        $departments = Department::select('id', 'name')
            ->withCount('users')
            ->get();

        $deptEnrollments = DB::table('course_enrollments')
            ->join('users', 'users.id', '=', 'course_enrollments.user_id')
            ->whereNotNull('users.department_id')
            ->whereBetween('course_enrollments.created_at', [$from, "$to 23:59:59"])
            ->select(
                'users.department_id',
                DB::raw('COUNT(*) as enrolled'),
                DB::raw("SUM(CASE WHEN course_enrollments.status = 'completed' THEN 1 ELSE 0 END) as completed"),
                DB::raw("SUM(CASE WHEN course_enrollments.status = 'in_progress' THEN 1 ELSE 0 END) as in_progress"),
                DB::raw("SUM(CASE WHEN course_enrollments.due_date IS NOT NULL AND course_enrollments.due_date < NOW() AND course_enrollments.status != 'completed' THEN 1 ELSE 0 END) as overdue")
            )
            ->groupBy('users.department_id')
            ->get()
            ->keyBy('department_id');

        $data = $departments->map(function ($dept) use ($deptEnrollments) {
            $stats = $deptEnrollments->get($dept->id);
            return [
                'department' => $dept->name,
                'users' => $dept->users_count,
                'enrolled' => $stats?->enrolled ?? 0,
                'completed' => $stats?->completed ?? 0,
                'in_progress' => $stats?->in_progress ?? 0,
                'overdue' => $stats?->overdue ?? 0,
                'completion_rate' => ($stats?->enrolled ?? 0) > 0
                    ? round(($stats->completed / $stats->enrolled) * 100, 1) : 0,
            ];
        })->sortByDesc('completion_rate')->values();

        if ($request->input('export') === 'csv') {
            return $this->exportCsv('department-breakdown', [
                'Department', 'Users', 'Enrolled', 'Completed', 'In Progress', 'Overdue', 'Completion %'
            ], $data->toArray());
        }

        return view('client.reports.department-breakdown', compact('data', 'from', 'to'));
    }

    /**
     * Overdue training — users with past-due courses.
     */
    public function overdueTraining(Request $request)
    {
        $baseQuery = CourseEnrollment::whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->where('status', '!=', 'completed')
            ->with(['user:id,name,email,department_id', 'user.department:id,name', 'course:id,title'])
            ->orderBy('due_date');

        if ($request->input('export') === 'csv') {
            $all = (clone $baseQuery)->get()->map(fn($e) => [
                'user' => $e->user->name,
                'email' => $e->user->email,
                'course' => $e->course->title,
                'due_date' => $e->due_date->format('Y-m-d'),
                'days_overdue' => now()->diffInDays($e->due_date),
                'progress' => $e->progress_percent . '%',
            ]);

            return $this->exportCsv('overdue-training', [
                'User', 'Email', 'Course', 'Due Date', 'Days Overdue', 'Progress'
            ], $all->toArray());
        }

        $overdue = $baseQuery->paginate(30);

        return view('client.reports.overdue-training', compact('overdue'));
    }

    // ── Advanced reports ──

    public function learningPathProgress()
    {
        $rows = $this->learningPathData();

        return view('client.reports.learning-path-progress', ['rows' => $rows]);
    }

    public function surveyAnalytics()
    {
        $surveys = $this->surveyData();

        return view('client.reports.survey-analytics', ['surveys' => $surveys]);
    }

    public function gamificationReport()
    {
        return view('client.reports.gamification-report', $this->gamificationData());
    }

    public function exportReport(string $type)
    {
        switch ($type) {
            case 'learning-paths':
                $rows = $this->learningPathData()->map(fn($r) => [
                    $r['title'], $r['enrolled'], $r['completed'], $r['rate'], $r['avg_days'] ?? '',
                ])->all();
                return $this->exportCsv('learning-path-progress',
                    ['Path', 'Enrolled', 'Completed', 'Completion %', 'Avg Days to Complete'], $rows);

            case 'surveys':
                $rows = $this->surveyData()->map(fn($s) => [
                    $s['title'], $s['responses'], $s['invited'], $s['response_rate'], $s['avg_rating'] ?? '',
                ])->all();
                return $this->exportCsv('survey-analytics',
                    ['Survey', 'Responses', 'Eligible Users', 'Response Rate %', 'Avg Rating'], $rows);

            case 'gamification':
                $rows = $this->gamificationData()['topUsers']->map(fn($u) => [
                    $u->name, $u->email, $u->period_points, $u->total_points,
                ])->all();
                return $this->exportCsv('gamification',
                    ['Name', 'Email', 'Points (all awards)', 'Total Points'], $rows);

            case 'user-progress':
                return redirect()->route('manage.reports.user-progress', ['export' => 'csv']);
            case 'department-breakdown':
                return redirect()->route('manage.reports.department-breakdown', ['export' => 'csv']);
            case 'overdue-training':
                return redirect()->route('manage.reports.overdue-training', ['export' => 'csv']);
        }

        abort(404);
    }

    private function tenantId(): ?int
    {
        return app()->bound('current_tenant_id') ? app('current_tenant_id') : null;
    }

    private function learningPathData()
    {
        // LearningPath uses BelongsToTenant, so it is already tenant scoped.
        $paths = \App\Models\LearningPath::orderBy('title')->get(['id', 'title']);

        $stats = DB::table('learning_path_enrollments')
            ->when($this->tenantId(), fn($q, $t) => $q->where('tenant_id', $t))
            ->whereIn('learning_path_id', $paths->pluck('id'))
            ->select(
                'learning_path_id',
                DB::raw('COUNT(*) as enrolled'),
                DB::raw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed")
            )
            ->groupBy('learning_path_id')->get()->keyBy('learning_path_id');

        // Average days computed in PHP for database portability.
        $durations = DB::table('learning_path_enrollments')
            ->when($this->tenantId(), fn($q, $t) => $q->where('tenant_id', $t))
            ->where('status', 'completed')
            ->whereNotNull('completed_at')
            ->get(['learning_path_id', 'started_at', 'created_at', 'completed_at'])
            ->groupBy('learning_path_id')
            ->map(fn($g) => round($g->avg(fn($e) => max(0,
                \Illuminate\Support\Carbon::parse($e->started_at ?? $e->created_at)
                    ->diffInSeconds(\Illuminate\Support\Carbon::parse($e->completed_at), true) / 86400
            )), 1));

        return $paths->map(function ($p) use ($stats, $durations) {
            $s = $stats->get($p->id);
            $enrolled = (int) ($s->enrolled ?? 0);
            $completed = (int) ($s->completed ?? 0);
            return [
                'title' => $p->title,
                'enrolled' => $enrolled,
                'completed' => $completed,
                'rate' => $enrolled > 0 ? round($completed / $enrolled * 100, 1) : 0,
                'avg_days' => $durations->get($p->id),
            ];
        })->values();
    }

    private function surveyData()
    {
        $surveys = \App\Models\Survey::with('questions')->orderBy('title')->get();
        $eligible = User::where('is_active', true)->count();

        return $surveys->map(function ($survey) use ($eligible) {
            $responses = \App\Models\SurveyResponse::where('survey_id', $survey->id)->get();
            $count = $responses->count();

            $byQuestion = [];
            foreach ($responses as $r) {
                foreach ((array) $r->answers as $a) {
                    if (isset($a['question_id'])) {
                        $byQuestion[$a['question_id']][] = $a['value'] ?? null;
                    }
                }
            }

            $questions = $survey->questions->map(function ($q) use ($byQuestion) {
                $vals = collect($byQuestion[$q->id] ?? [])->filter(fn($v) => $v !== null && $v !== '');
                $numeric = in_array($q->type, ['rating', 'scale'], true);
                return [
                    'question' => $q->question,
                    'type' => $q->type,
                    'answers' => $vals->count(),
                    'avg' => $numeric && $vals->count() ? round($vals->avg(), 2) : null,
                    'distribution' => in_array($q->type, ['multiple_choice', 'yes_no', 'rating', 'scale'], true)
                        ? $vals->map(fn($v) => is_array($v) ? implode(', ', $v) : (string) $v)->countBy()->sortKeys()->all()
                        : [],
                ];
            });

            $rated = $responses->pluck('overall_rating')->filter();
            $trend = $responses->groupBy(fn($r) => \Illuminate\Support\Carbon::parse($r->submitted_at)->format('Y-m'))
                ->map(fn($g) => ['count' => $g->count(), 'avg' => round($g->pluck('overall_rating')->filter()->avg() ?? 0, 2)])
                ->sortKeys()->all();

            return [
                'title' => $survey->title,
                'responses' => $count,
                'invited' => $eligible,
                'response_rate' => $eligible > 0 ? round($count / $eligible * 100, 1) : 0,
                'avg_rating' => $rated->count() ? round($rated->avg(), 2) : null,
                'questions' => $questions,
                'trend' => $trend,
            ];
        });
    }

    private function gamificationData(): array
    {
        $tid = $this->tenantId();
        $scope = fn($q) => $q->when($tid, fn($qq) => $qq->where('tenant_id', $tid));

        $totalPoints = (int) $scope(DB::table('gamification_points'))->sum('points');
        $activeUsers = User::where('is_active', true)->count();

        $topUsers = User::select('id', 'name', 'email', 'total_points')
            ->withSum('gamificationPoints as period_points', 'points')
            ->orderByDesc('total_points')->limit(10)->get();

        $byAction = $scope(DB::table('gamification_points'))
            ->select('action', DB::raw('SUM(points) as points'), DB::raw('COUNT(*) as awards'))
            ->groupBy('action')->orderByDesc('points')->get();

        $bands = ['0-99' => [0, 99], '100-499' => [100, 499], '500-999' => [500, 999], '1000+' => [1000, PHP_INT_MAX]];
        $distribution = [];
        foreach ($bands as $label => [$lo, $hi]) {
            $distribution[$label] = User::where('is_active', true)->whereBetween('total_points', [$lo, min($hi, 2147483647)])->count();
        }

        $badges = DB::table('badges')
            ->leftJoin('user_badges', function ($j) use ($tid) {
                $j->on('user_badges.badge_id', '=', 'badges.id');
                if ($tid) {
                    $j->where('user_badges.tenant_id', $tid);
                }
            })
            ->select('badges.name', 'badges.icon', DB::raw('COUNT(user_badges.id) as earned'))
            ->groupBy('badges.id', 'badges.name', 'badges.icon')
            ->orderByDesc('earned')->get();

        $streaks = $scope(DB::table('user_streaks'))
            ->selectRaw('AVG(current_streak) as avg_current, MAX(longest_streak) as best, SUM(CASE WHEN current_streak > 0 THEN 1 ELSE 0 END) as active')
            ->first();

        return [
            'totalPoints' => $totalPoints,
            'avgPerUser' => $activeUsers ? round($totalPoints / $activeUsers, 1) : 0,
            'topUsers' => $topUsers,
            'byAction' => $byAction,
            'distribution' => $distribution,
            'badges' => $badges,
            'streaks' => $streaks,
        ];
    }

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
