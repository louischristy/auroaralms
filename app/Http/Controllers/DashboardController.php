<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Department;
use App\Models\LessonCompletion;
use App\Models\Policy;
use App\Models\PolicyAcknowledgment;
use App\Models\QuizAttempt;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->isPlatformAdmin()) {
            return $this->platformDashboard();
        }

        if ($user->isClientAdmin()) {
            return $this->clientDashboard($user);
        }

        if ($user->isManager()) {
            return $this->managerDashboard($user);
        }

        return $this->employeeDashboard($user);
    }

    private function platformDashboard()
    {
        $totalUsers = User::withoutTenantScope()->whereNotNull('tenant_id')->count();
        $totalEnrollments = CourseEnrollment::withoutTenantScope()->count();
        $completedEnrollments = CourseEnrollment::withoutTenantScope()->where('status', 'completed')->count();
        $totalCertificates = Certificate::withoutTenantScope()->count();

        // Completion rate by tenant
        $tenantStats = Tenant::where('is_active', true)
            ->withCount(['users'])
            ->get()
            ->map(function ($tenant) {
                $enrollments = CourseEnrollment::withoutTenantScope()
                    ->where('tenant_id', $tenant->id)->count();
                $completed = CourseEnrollment::withoutTenantScope()
                    ->where('tenant_id', $tenant->id)->where('status', 'completed')->count();
                $tenant->enrollment_count = $enrollments;
                $tenant->completed_count = $completed;
                $tenant->completion_rate = $enrollments > 0 ? round(($completed / $enrollments) * 100) : 0;
                return $tenant;
            })
            ->sortByDesc('completion_rate');

        // Recent completions
        $recentCompletions = CourseEnrollment::withoutTenantScope()
            ->where('status', 'completed')
            ->with(['user', 'course'])
            ->orderByDesc('completed_at')
            ->limit(10)
            ->get();

        // Course popularity
        $courseStats = Course::withCount([
            'enrollments as total_enrollments' => fn ($q) => $q->withoutTenantScope(),
            'enrollments as completed_enrollments' => fn ($q) => $q->withoutTenantScope()->where('status', 'completed'),
        ])->where('is_active', true)->orderByDesc('total_enrollments')->limit(10)->get();

        return view('dashboard.platform', [
            'stats' => [
                'total_tenants' => Tenant::count(),
                'active_tenants' => Tenant::where('is_active', true)->count(),
                'total_users' => $totalUsers,
                'total_courses' => Course::where('is_active', true)->count(),
                'total_enrollments' => $totalEnrollments,
                'completed_enrollments' => $completedEnrollments,
                'completion_rate' => $totalEnrollments > 0 ? round(($completedEnrollments / $totalEnrollments) * 100) : 0,
                'total_certificates' => $totalCertificates,
            ],
            'tenantStats' => $tenantStats,
            'recentCompletions' => $recentCompletions,
            'courseStats' => $courseStats,
        ]);
    }

    private function clientDashboard($user)
    {
        $totalUsers = User::count();
        $totalEnrollments = CourseEnrollment::count();
        $completedEnrollments = CourseEnrollment::where('status', 'completed')->count();
        $overdueCount = CourseEnrollment::whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->where('status', '!=', 'completed')
            ->count();

        // Policy compliance
        $publishedPolicies = Policy::where('is_published', true)->where('requires_acknowledgment', true)->count();
        $totalAcks = PolicyAcknowledgment::count();
        $expectedAcks = $publishedPolicies * $totalUsers;
        $policyCompliance = $expectedAcks > 0 ? round(($totalAcks / $expectedAcks) * 100) : 100;

        // Department completion rates
        $departments = Department::withCount('users')->get()->map(function ($dept) {
            $userIds = User::where('department_id', $dept->id)->pluck('id');
            $enrolled = CourseEnrollment::whereIn('user_id', $userIds)->count();
            $completed = CourseEnrollment::whereIn('user_id', $userIds)->where('status', 'completed')->count();
            $dept->completion_rate = $enrolled > 0 ? round(($completed / $enrolled) * 100) : 0;
            return $dept;
        })->sortByDesc('completion_rate');

        // Users needing attention (overdue or not started)
        $usersNeedingAttention = User::whereHas('courseEnrollments', function ($q) {
            $q->where(function ($q2) {
                $q2->where('status', 'not_started')
                    ->orWhere(function ($q3) {
                        $q3->whereNotNull('due_date')->where('due_date', '<', now())->where('status', '!=', 'completed');
                    });
            });
        })->with(['courseEnrollments' => function ($q) {
            $q->where('status', '!=', 'completed');
        }, 'courseEnrollments.course', 'department'])->limit(10)->get();

        return view('dashboard.client', [
            'stats' => [
                'total_users' => $totalUsers,
                'total_departments' => Department::count(),
                'total_enrollments' => $totalEnrollments,
                'completed_enrollments' => $completedEnrollments,
                'completion_rate' => $totalEnrollments > 0 ? round(($completedEnrollments / $totalEnrollments) * 100) : 0,
                'overdue_count' => $overdueCount,
                'policy_compliance' => $policyCompliance,
                'certificates_issued' => Certificate::count(),
            ],
            'departments' => $departments,
            'usersNeedingAttention' => $usersNeedingAttention,
        ]);
    }

    private function managerDashboard($user)
    {
        $teamUserIds = User::where('department_id', $user->department_id)->pluck('id');
        $teamSize = $teamUserIds->count();
        $enrolled = CourseEnrollment::whereIn('user_id', $teamUserIds)->count();
        $completed = CourseEnrollment::whereIn('user_id', $teamUserIds)->where('status', 'completed')->count();

        $teamMembers = User::where('department_id', $user->department_id)
            ->withCount([
                'courseEnrollments',
                'courseEnrollments as completed_courses_count' => fn ($q) => $q->where('status', 'completed'),
            ])
            ->get()
            ->map(function ($member) {
                $member->completion_rate = $member->course_enrollments_count > 0
                    ? round(($member->completed_courses_count / $member->course_enrollments_count) * 100)
                    : 0;
                return $member;
            })
            ->sortBy('completion_rate');

        return view('dashboard.manager', [
            'stats' => [
                'team_size' => $teamSize,
                'enrolled' => $enrolled,
                'completed' => $completed,
                'completion_rate' => $enrolled > 0 ? round(($completed / $enrolled) * 100) : 0,
            ],
            'teamMembers' => $teamMembers,
        ]);
    }

    private function employeeDashboard($user)
    {
        $enrollments = CourseEnrollment::where('user_id', $user->id)
            ->with('course')
            ->get();

        $inProgress = $enrollments->where('status', 'in_progress');
        $completed = $enrollments->where('status', 'completed');
        $overdue = $enrollments->filter(fn ($e) => $e->isOverdue());

        $certificates = Certificate::where('user_id', $user->id)->count();

        // Pending policies
        $pendingPolicies = Policy::where('is_published', true)
            ->where('requires_acknowledgment', true)
            ->whereDoesntHave('acknowledgments', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->count();

        // Recent activity
        $recentLessons = LessonCompletion::where('user_id', $user->id)
            ->with(['lesson', 'lesson.course'])
            ->orderByDesc('completed_at')
            ->limit(5)
            ->get();

        return view('dashboard.employee', [
            'user' => $user,
            'stats' => [
                'total_enrolled' => $enrollments->count(),
                'in_progress' => $inProgress->count(),
                'completed' => $completed->count(),
                'overdue' => $overdue->count(),
                'certificates' => $certificates,
                'pending_policies' => $pendingPolicies,
            ],
            'inProgressCourses' => $inProgress,
            'recentLessons' => $recentLessons,
        ]);
    }

    public function teamReports(Request $request)
    {
        return view('reports.team');
    }
}
