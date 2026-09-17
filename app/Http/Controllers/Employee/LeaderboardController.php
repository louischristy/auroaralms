<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Models\UserBadge;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class LeaderboardController extends Controller
{
    public function index()
    {
        $currentUser = Auth::user();
        $tenantId = $currentUser->tenant_id;

        // Cache leaderboard for 5 minutes per tenant
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
                    $user->total_points = ($user->courses_completed * 100)
                        + ($user->passed_quizzes * 50)
                        + ($user->badges_count * 25);
                    return $user;
                })
                ->sortByDesc('total_points')
                ->values()
                ->map(function ($user, $index) {
                    $user->rank = $index + 1;
                    return $user;
                });
        });

        // Current user stats
        $currentUserEntry = $leaderboard->firstWhere('id', $currentUser->id);
        $currentUserRank = $currentUserEntry?->rank ?? $leaderboard->count();
        $currentUserPoints = $currentUserEntry?->total_points ?? 0;
        $currentUserBadgesCount = $currentUserEntry?->badges_count ?? 0;

        // Trim to top 20 for display
        $top20 = $leaderboard->take(20);

        // Badges
        $allBadges = Badge::active()->orderBy('criteria_value')->get();
        $earnedBadges = UserBadge::where('user_id', $currentUser->id)
            ->with('badge')
            ->get()
            ->keyBy('badge_id');

        return view('employee.leaderboard.index', [
            'leaderboard' => $top20,
            'currentUserId' => $currentUser->id,
            'currentUserRank' => $currentUserRank,
            'currentUserPoints' => $currentUserPoints,
            'currentUserBadgesCount' => $currentUserBadgesCount,
            'allBadges' => $allBadges,
            'earnedBadges' => $earnedBadges,
            'totalBadges' => $allBadges->count(),
        ]);
    }
}
