<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Department;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Http\Request;
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

        $users->getCollection()->transform(function ($user) {
            $user->completion_rate = $user->enrolled > 0
                ? round(($user->completed / $user->enrolled) * 100, 1) : 0;
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
        $overdue = CourseEnrollment::whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->where('status', '!=', 'completed')
            ->with(['user:id,name,email,department_id', 'user.department:id,name', 'course:id,title'])
            ->orderBy('due_date')
            ->paginate(30);

        if ($request->input('export') === 'csv') {
            $all = CourseEnrollment::whereNotNull('due_date')
                ->where('due_date', '<', now())
                ->where('status', '!=', 'completed')
                ->with(['user:id,name,email', 'course:id,title'])
                ->orderBy('due_date')
                ->get()
                ->map(fn($e) => [
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

        return view('client.reports.overdue-training', compact('overdue'));
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
