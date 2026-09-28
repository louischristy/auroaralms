<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Models\Department;
use App\Models\User;
use App\Models\UserBadge;
use App\Models\UserStreak;
use App\Services\GamificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class LeaderboardController extends Controller
{
    private const PERIODS = ['all_time', 'this_month', 'this_week'];
    private const TABS = ['points', 'badges', 'departments'];

    public function index(Request $request)
    {
        $currentUser = Auth::user();
        $tenantId = $currentUser->tenant_id;

        $tab = in_array($request->query('tab'), self::TABS, true) ? $request->query('tab') : 'points';
        $period = in_array($request->query('period'), self::PERIODS, true) ? $request->query('period') : 'all_time';

        // Badge-based leaderboard (existing), cached 5 minutes per tenant
        $leaderboard = Cache::remember("leaderboard:{$tenantId}", 300, function () use ($tenantId) {
            return User::where('tenant_id', $tenantId)
                ->where('is_active', true)
                ->with('department:id,name')
                ->withCount([
                    'courseEnrollments as courses_completed' => fn($q) => $q->where('status', 'completed'),
                    'quizAttempts as passed_quizzes' => fn($q) => $q->where('passed', true),
                    'badges as badges_count',
                ])
                ->get()
                ->map(function ($user) {
                    $user->badge_score = ($user->courses_completed * 100)
                        + ($user->passed_quizzes * 50)
                        + ($user->badges_count * 25);
                    return $user;
                })
                ->sortByDesc('badge_score')
                ->values()
                ->map(function ($user, $index) {
                    $user->rank = $index + 1;
                    return $user;
                });
        });

        // Points leaderboard (full tenant list so the current user's rank is exact)
        $pointsFull = Cache::remember("leaderboard:points:{$tenantId}:{$period}", 120, fn() =>
            GamificationService::getLeaderboard($tenantId, 100000, $period)
        );
        $pointsBoard = $pointsFull->take(20);

        $myPointsEntry = $pointsFull->firstWhere('id', $currentUser->id);
        $myBadgeEntry = $leaderboard->firstWhere('id', $currentUser->id);
        $streak = UserStreak::withoutTenantScope()
            ->where('user_id', $currentUser->id)
            ->orderByDesc('last_activity_date')
            ->first();

        // Department leaderboard
        $departments = Cache::remember("leaderboard:departments:{$tenantId}", 300, function () use ($tenantId) {
            return User::where('tenant_id', $tenantId)
                ->where('is_active', true)
                ->whereNotNull('department_id')
                ->withCount('badges as badges_count')
                ->get()
                ->groupBy('department_id')
                ->map(fn($members, $deptId) => (object) [
                    'name' => Department::withoutGlobalScopes()->find($deptId)?->name ?? 'Unknown',
                    'avg_points' => round($members->avg('total_points')),
                    'total_badges' => $members->sum('badges_count'),
                    'member_count' => $members->count(),
                ])
                ->sortByDesc('avg_points')
                ->values()
                ->map(function ($d, $i) {
                    $d->rank = $i + 1;
                    return $d;
                });
        });

        $allBadges = Badge::active()->orderBy('criteria_value')->get();
        $earnedBadges = UserBadge::where('user_id', $currentUser->id)
            ->with('badge')
            ->get()
            ->keyBy('badge_id');

        return view('employee.leaderboard.index', [
            'tab' => $tab,
            'period' => $period,
            'leaderboard' => $leaderboard->take(20),
            'pointsBoard' => $pointsBoard,
            'departments' => $departments,
            'currentUserId' => $currentUser->id,
            'currentUserRank' => $myPointsEntry?->rank ?? $pointsFull->count(),
            'currentUserBadgeRank' => $myBadgeEntry?->rank ?? $leaderboard->count(),
            'currentUserPoints' => $myPointsEntry?->points ?? (int) $currentUser->total_points,
            'currentStreak' => $streak?->current_streak ?? 0,
            'longestStreak' => $streak?->longest_streak ?? 0,
            'currentUserBadgesCount' => $earnedBadges->count(),
            'allBadges' => $allBadges,
            'earnedBadges' => $earnedBadges,
            'totalBadges' => $allBadges->count(),
        ]);
    }
}
