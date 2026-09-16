<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Models\CourseEnrollment;
use App\Models\QuizAttempt;
use App\Models\UserBadge;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class LeaderboardController extends Controller
{
    public function index()
    {
        $currentUser = Auth::user();
        $tenantId = $currentUser->tenant_id;

        // Get all active tenant users and compute their stats
        $users = User::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->with('department')
            ->get();

        $leaderboard = $users->map(function ($user) {
            $coursesCompleted = CourseEnrollment::withoutTenantScope()
                ->where('user_id', $user->id)
                ->where('status', 'completed')
                ->count();
            $passedQuizzes = QuizAttempt::where('user_id', $user->id)
                ->where('passed', true)
                ->count();
            $badgesCount = UserBadge::withoutTenantScope()
                ->where('user_id', $user->id)
                ->count();

            $user->courses_completed = $coursesCompleted;
            $user->passed_quizzes = $passedQuizzes;
            $user->badges_count = $badgesCount;
            $user->total_points = ($coursesCompleted * 100) + ($passedQuizzes * 50) + ($badgesCount * 25);

            return $user;
        })
        ->sortByDesc('total_points')
        ->values();

        // Add ranks
        $leaderboard = $leaderboard->map(function ($user, $index) {
            $user->rank = $index + 1;
            return $user;
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
