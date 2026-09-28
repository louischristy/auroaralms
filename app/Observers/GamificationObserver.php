<?php

namespace App\Observers;

use App\Models\CourseEnrollment;
use App\Models\GamificationPoint;
use App\Models\LessonCompletion;
use App\Models\QuizAttempt;
use App\Models\SurveyResponse;
use App\Services\GamificationService;
use Illuminate\Database\Eloquent\Model;

/**
 * Registered against several models; dispatches by model type.
 */
class GamificationObserver
{
    public function created(Model $model): void
    {
        $config = GamificationService::getPointsConfig();

        if ($model instanceof LessonCompletion) {
            GamificationService::awardPoints($model->user_id, $config['lesson_completed'], 'lesson_completed', 'lesson', $model->lesson_id, 'Completed a lesson');
        } elseif ($model instanceof SurveyResponse) {
            GamificationService::awardPoints($model->user_id, $config['survey_completed'], 'survey_completed', 'survey', $model->survey_id, 'Completed a survey');
        } elseif ($model instanceof QuizAttempt) {
            $this->awardQuiz($model);
        } elseif ($model instanceof CourseEnrollment) {
            $this->awardCourse($model);
        }
    }

    public function updated(Model $model): void
    {
        if ($model instanceof CourseEnrollment && $model->wasChanged('status')) {
            $this->awardCourse($model);
        } elseif ($model instanceof QuizAttempt && $model->wasChanged('passed')) {
            $this->awardQuiz($model);
        }
    }

    private function awardCourse(CourseEnrollment $enrollment): void
    {
        if ($enrollment->status !== 'completed' || $this->alreadyAwarded($enrollment->user_id, 'course_completed', 'course', $enrollment->course_id)) {
            return;
        }
        GamificationService::awardPoints($enrollment->user_id, GamificationService::getPointsConfig()['course_completed'], 'course_completed', 'course', $enrollment->course_id, 'Completed a course');
    }

    private function awardQuiz(QuizAttempt $attempt): void
    {
        if (!$attempt->passed || $this->alreadyAwarded($attempt->user_id, 'quiz_passed', 'quiz_attempt', $attempt->id)) {
            return;
        }
        $config = GamificationService::getPointsConfig();
        $perfect = (float) $attempt->score >= 100;
        GamificationService::awardPoints(
            $attempt->user_id,
            $perfect ? $config['quiz_perfect_score'] : $config['quiz_passed'],
            $perfect ? 'quiz_perfect_score' : 'quiz_passed',
            'quiz_attempt',
            $attempt->id,
            $perfect ? 'Perfect quiz score' : 'Passed a quiz'
        );
    }

    /** Prevent double awards (e.g. re-saving or re-completing the same item). */
    private function alreadyAwarded(int $userId, string $action, string $refType, $refId): bool
    {
        return GamificationPoint::withoutTenantScope()
            ->where('user_id', $userId)
            ->whereIn('action', $action === 'quiz_passed' ? ['quiz_passed', 'quiz_perfect_score'] : [$action])
            ->where('reference_type', $refType)
            ->where('reference_id', $refId)
            ->exists();
    }
}
