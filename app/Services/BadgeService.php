<?php

namespace App\Services;

use App\Models\Badge;
use App\Models\CourseEnrollment;
use App\Models\QuizAttempt;
use App\Models\User;
use App\Models\UserBadge;

class BadgeService
{
    /**
     * Evaluate all active badges for a user and award any newly earned ones.
     *
     * @return array<Badge> Newly awarded badges
     */
    public function evaluate(User $user): array
    {
        $badges = Badge::active()->get();
        $earnedBadgeIds = UserBadge::where('user_id', $user->id)->pluck('badge_id')->toArray();
        $awarded = [];

        $stats = $this->getUserStats($user);

        foreach ($badges as $badge) {
            if (in_array($badge->id, $earnedBadgeIds)) {
                continue;
            }

            if ($this->meetsCriteria($badge, $stats)) {
                UserBadge::create([
                    'user_id' => $user->id,
                    'badge_id' => $badge->id,
                    'tenant_id' => $user->tenant_id,
                    'earned_at' => now(),
                ]);
                $awarded[] = $badge;
            }
        }

        return $awarded;
    }

    private function getUserStats(User $user): array
    {
        return [
            'courses_completed' => $this->countCompletedCourses($user),
            'quizzes_passed' => $this->countPassedQuizzes($user),
            'perfect_scores' => $this->countPerfectScores($user),
            'phishing_reports' => $this->countPhishingReports($user),
        ];
    }

    private function meetsCriteria(Badge $badge, array $stats): bool
    {
        return match ($badge->criteria_type) {
            'first_completion' => $stats['courses_completed'] >= 1,
            'courses_completed' => $stats['courses_completed'] >= $badge->criteria_value,
            'quizzes_passed' => $stats['quizzes_passed'] >= $badge->criteria_value,
            'perfect_scores' => $stats['perfect_scores'] >= $badge->criteria_value,
            'phishing_reporter' => $stats['phishing_reports'] >= $badge->criteria_value,
            'streak_days' => false, // Future implementation
            default => false,
        };
    }

    private function countCompletedCourses(User $user): int
    {
        return CourseEnrollment::withoutTenantScope()
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->count();
    }

    private function countPassedQuizzes(User $user): int
    {
        return QuizAttempt::where('user_id', $user->id)
            ->where('passed', true)
            ->count();
    }

    private function countPerfectScores(User $user): int
    {
        return QuizAttempt::where('user_id', $user->id)
            ->where('score', 100)
            ->count();
    }

    private function countPhishingReports(User $user): int
    {
        return \App\Models\PhishingResult::where('user_id', $user->id)
            ->where('status', 'reported')
            ->count();
    }
}
