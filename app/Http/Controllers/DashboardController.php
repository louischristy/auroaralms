<?php

namespace App\Http\Controllers;

use App\Models\Badge;
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
use App\Models\UserBadge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
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
        $stats = Cache::remember('platform_dashboard_stats', 300, function () {
            $totalEnrollments = CourseEnrollment::withoutTenantScope()->count();
            $completedEnrollments = CourseEnrollment::withoutTenantScope()->where('status', 'completed')->count();

            return [
                'total_tenants' => Tenant::count(),
                'active_tenants' => Tenant::where('is_active', true)->count(),
                'total_users' => User::withoutTenantScope()->whereNotNull('tenant_id')->count(),
                'total_courses' => Course::where('is_active', true)->count(),
                'total_enrollments' => $totalEnrollments,
                'completed_enrollments' => $completedEnrollments,
                'completion_rate' => $totalEnrollments > 0 ? round(($completedEnrollments / $totalEnrollments) * 100) : 0,
                'total_certificates' => Certificate::withoutTenantScope()->count(),
            ];
        });

        // Completion rate by tenant - use aggregate query instead of N+1
        $tenantStats = Cache::remember('platform_dashboard_tenant_stats', 300, function () {

            // Simpler approach: get stats as keyed array
            $enrollmentsByTenant = CourseEnrollment::withoutTenantScope()
                ->select('tenant_id',
                    DB::raw('COUNT(*) as enrollment_count'),
                    DB::raw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_count")
                )
                ->groupBy('tenant_id')
                ->get()
                ->keyBy('tenant_id');

            return Tenant::where('is_active', true)
                ->select('id', 'name', 'slug')
                ->withCount('users')
                ->get()
                ->map(function ($tenant) use ($enrollmentsByTenant) {
                    $stats = $enrollmentsByTenant->get($tenant->id);
                    $tenant->enrollment_count = $stats?->enrollment_count ?? 0;
                    $tenant->completed_count = $stats?->completed_count ?? 0;
                    $tenant->completion_rate = $tenant->enrollment_count > 0
                        ? round(($tenant->completed_count / $tenant->enrollment_count) * 100)
                        : 0;
                    return $tenant;
                })
                ->sortByDesc('completion_rate');
        });

        // Recent completions
        $recentCompletions = CourseEnrollment::withoutTenantScope()
            ->where('status', 'completed')
            ->with(['user:id,name,email', 'course:id,title,slug'])
            ->orderByDesc('completed_at')
            ->limit(10)
            ->get();

        // Course popularity
        $courseStats = Course::select('id', 'title', 'slug', 'category', 'is_active')
            ->withCount([
                'enrollments as total_enrollments' => fn ($q) => $q->withoutTenantScope(),
                'enrollments as completed_enrollments' => fn ($q) => $q->withoutTenantScope()->where('status', 'completed'),
            ])->where('is_active', true)->orderByDesc('total_enrollments')->limit(10)->get();

        return view('dashboard.platform', [
            'stats' => $stats,
            'tenantStats' => $tenantStats,
            'recentCompletions' => $recentCompletions,
            'courseStats' => $courseStats,
        ]);
    }

    private function clientDashboard($user)
    {
        $tenantId = $user->tenant_id;

        $stats = Cache::remember("client_dashboard_stats_{$tenantId}", 300, function () {
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

            return [
                'total_users' => $totalUsers,
                'total_departments' => Department::count(),
                'total_enrollments' => $totalEnrollments,
                'completed_enrollments' => $completedEnrollments,
                'completion_rate' => $totalEnrollments > 0 ? round(($completedEnrollments / $totalEnrollments) * 100) : 0,
                'overdue_count' => $overdueCount,
                'policy_compliance' => $policyCompliance,
                'certificates_issued' => Certificate::count(),
            ];
        });

        // Department completion rates - use aggregate query instead of N+1
        $departments = Cache::remember("client_dashboard_depts_{$tenantId}", 300, function () {
            $deptEnrollments = DB::table('course_enrollments')
                ->join('users', 'users.id', '=', 'course_enrollments.user_id')
                ->whereNotNull('users.department_id')
                ->select(
                    'users.department_id',
                    DB::raw('COUNT(*) as enrolled'),
                    DB::raw("SUM(CASE WHEN course_enrollments.status = 'completed' THEN 1 ELSE 0 END) as completed")
                )
                ->groupBy('users.department_id')
                ->get()
                ->keyBy('department_id');

            return Department::select('id', 'name')
                ->withCount('users')
                ->get()
                ->map(function ($dept) use ($deptEnrollments) {
                    $stats = $deptEnrollments->get($dept->id);
                    $enrolled = $stats?->enrolled ?? 0;
                    $completed = $stats?->completed ?? 0;
                    $dept->completion_rate = $enrolled > 0 ? round(($completed / $enrolled) * 100) : 0;
                    return $dept;
                })
                ->sortByDesc('completion_rate');
        });

        // Users needing attention (overdue or not started)
        $usersNeedingAttention = User::select('id', 'name', 'email', 'department_id')
            ->whereHas('courseEnrollments', function ($q) {
                $q->where(function ($q2) {
                    $q2->where('status', 'not_started')
                        ->orWhere(function ($q3) {
                            $q3->whereNotNull('due_date')->where('due_date', '<', now())->where('status', '!=', 'completed');
                        });
                });
            })->with(['courseEnrollments' => function ($q) {
                $q->where('status', '!=', 'completed')->select('id', 'user_id', 'course_id', 'status', 'due_date', 'progress_percent');
            }, 'courseEnrollments.course:id,title,slug', 'department:id,name'])->limit(10)->get();

        return view('dashboard.client', [
            'stats' => $stats,
            'departments' => $departments,
            'usersNeedingAttention' => $usersNeedingAttention,
        ]);
    }

    private function managerDashboard($user)
    {
        $departmentId = $user->department_id;

        $teamUserIds = User::where('department_id', $departmentId)->pluck('id');
        $teamSize = $teamUserIds->count();

        $enrollmentStats = CourseEnrollment::whereIn('user_id', $teamUserIds)
            ->select(
                DB::raw('COUNT(*) as enrolled'),
                DB::raw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed")
            )
            ->first();

        $enrolled = $enrollmentStats->enrolled ?? 0;
        $completed = $enrollmentStats->completed ?? 0;

        $teamMembers = User::select('id', 'name', 'email')
            ->where('department_id', $departmentId)
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
            ->with('course:id,title,slug,category,thumbnail_path,duration_minutes')
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
            ->with(['lesson:id,title,course_id', 'lesson.course:id,title,slug'])
            ->orderByDesc('completed_at')
            ->limit(5)
            ->get();

        // Gamification stats
        $passedQuizzes = QuizAttempt::where('user_id', $user->id)->where('passed', true)->count();
        $badgesCount = UserBadge::where('user_id', $user->id)->count();
        $totalPoints = ($completed->count() * 100) + ($passedQuizzes * 50) + ($badgesCount * 25);

        // Recent badges (last 3)
        $recentBadges = UserBadge::where('user_id', $user->id)
            ->with('badge')
            ->orderByDesc('earned_at')
            ->limit(3)
            ->get();

        // User's rank within tenant
        $tenantUsers = User::where('tenant_id', $user->tenant_id)
            ->where('is_active', true)
            ->withCount([
                'courseEnrollments as courses_completed' => fn($q) => $q->where('status', 'completed'),
                'quizAttempts as passed_quizzes' => fn($q) => $q->where('passed', true),
                'badges as badges_count',
            ])
            ->get()
            ->map(function ($u) {
                $u->total_points = ($u->courses_completed * 100) + ($u->passed_quizzes * 50) + ($u->badges_count * 25);
                return $u;
            })
            ->sortByDesc('total_points')
            ->values();
        $rank = $tenantUsers->search(fn($u) => $u->id === $user->id);
        $userRank = $rank !== false ? $rank + 1 : $tenantUsers->count();

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
            'gamification' => [
                'total_points' => $totalPoints,
                'badges_count' => $badgesCount,
                'total_badges' => Badge::active()->count(),
                'rank' => $userRank,
                'recent_badges' => $recentBadges,
            ],
        ]);
    }

    public function teamReports(Request $request)
    {
        return view('reports.team');
    }
}
