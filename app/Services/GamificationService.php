<?php

namespace App\Services;

use App\Models\GamificationPoint;
use App\Models\User;
use App\Models\UserStreak;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class GamificationService
{
    public const STREAK_ACTIONS = ['login', 'lesson_completed'];

    public static function getPointsConfig(): array
    {
        return [
            'lesson_completed' => 10,
            'course_completed' => 50,
            'quiz_passed' => 30,
            'quiz_perfect_score' => 50,
            'badge_earned' => 25,
            'survey_completed' => 15,
            'login_streak_7' => 50,
            'login_streak_30' => 200,
            'learning_path_completed' => 100,
        ];
    }

    public static function awardPoints(
        $userId,
        $points,
        $action,
        $referenceType = null,
        $referenceId = null,
        $description = null
    ): GamificationPoint {
        $tenantId = User::withoutGlobalScopes()->whereKey($userId)->value('tenant_id');

        $record = DB::transaction(function () use ($userId, $tenantId, $points, $action, $referenceType, $referenceId, $description) {
            $record = GamificationPoint::withoutTenantScope()->create([
                'user_id' => $userId,
                'tenant_id' => $tenantId,
                'points' => $points,
                'action' => $action,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'description' => $description,
            ]);

            User::withoutGlobalScopes()->whereKey($userId)->increment('total_points', (int) $points);

            return $record;
        });

        if (in_array($action, self::STREAK_ACTIONS, true)) {
            self::updateStreak($userId, $tenantId);
        }

        return $record;
    }

    /**
     * @return Collection of users with points, rank (and streak/badge counts).
     */
    public static function getLeaderboard($tenantId, $limit = 10, $period = 'all_time'): Collection
    {
        $query = User::withoutGlobalScopes()
            ->where('users.tenant_id', $tenantId)
            ->where('users.is_active', true)
            ->with('department:id,name')
            ->withCount('badges as badges_count')
            ->addSelect([
                'current_streak' => UserStreak::withoutTenantScope()
                    ->select('current_streak')
                    ->whereColumn('user_streaks.user_id', 'users.id')
                    ->orderByDesc('last_activity_date')
                    ->limit(1),
            ]);

        $since = match ($period) {
            'this_month' => now()->startOfMonth(),
            'this_week' => now()->startOfWeek(),
            default => null,
        };

        if ($since) {
            $query->addSelect([
                'users.*',
                'period_points' => GamificationPoint::withoutTenantScope()
                    ->selectRaw('COALESCE(SUM(points), 0)')
                    ->whereColumn('gamification_points.user_id', 'users.id')
                    ->where('gamification_points.created_at', '>=', $since),
            ])->orderByDesc('period_points');
        } else {
            $query->addSelect('users.*')->orderByDesc('users.total_points');
        }

        return $query->orderBy('users.name')
            ->limit((int) $limit)
            ->get()
            ->values()
            ->map(function ($user, $index) use ($since) {
                $user->points = $since ? (int) $user->period_points : (int) $user->total_points;
                $user->current_streak = (int) ($user->current_streak ?? 0);
                $user->rank = $index + 1;
                return $user;
            });
    }

    public static function updateStreak($userId, $tenantId = null): UserStreak
    {
        $tenantId ??= User::withoutGlobalScopes()->whereKey($userId)->value('tenant_id');
        $today = Carbon::today();

        $streak = UserStreak::withoutTenantScope()->firstOrCreate(
            ['user_id' => $userId, 'tenant_id' => $tenantId],
            ['current_streak' => 0, 'longest_streak' => 0]
        );

        $last = $streak->last_activity_date?->copy()->startOfDay();

        if ($last && $last->equalTo($today)) {
            return $streak;
        }

        $streak->current_streak = ($last && $last->equalTo($today->copy()->subDay()))
            ? $streak->current_streak + 1
            : 1;
        $streak->longest_streak = max($streak->longest_streak, $streak->current_streak);
        $streak->last_activity_date = $today;
        $streak->save();

        // Milestone bonuses (these actions do not re-trigger streak updates)
        $config = self::getPointsConfig();
        foreach ([7 => 'login_streak_7', 30 => 'login_streak_30'] as $days => $action) {
            if ($streak->current_streak === $days) {
                self::awardPoints($userId, $config[$action], $action, null, null, "{$days}-day activity streak");
            }
        }

        return $streak;
    }
}
