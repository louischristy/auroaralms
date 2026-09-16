<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseEnrollment extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'user_id', 'course_id', 'tenant_id', 'status', 'progress_percent',
        'started_at', 'completed_at', 'due_date',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'due_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    // ── Status helpers ──

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isOverdue(): bool
    {
        return $this->due_date && !$this->isCompleted() && $this->due_date->isPast();
    }

    public function recalculateProgress(): void
    {
        $course = $this->course;
        $totalLessons = $course->lessons()->where('is_active', true)->count();
        $completedLessons = LessonCompletion::where('user_id', $this->user_id)
            ->where('course_id', $this->course_id)
            ->count();

        $hasQuiz = $course->quiz()->exists();
        $quizPassed = QuizAttempt::where('user_id', $this->user_id)
            ->where('course_id', $this->course_id)
            ->where('passed', true)
            ->exists();

        // Progress: lessons count for 80%, quiz for 20% (if quiz exists)
        if ($totalLessons === 0 && !$hasQuiz) {
            $this->progress_percent = 0;
            $this->save();
            return;
        }

        if ($hasQuiz) {
            $lessonProgress = $totalLessons > 0 ? ($completedLessons / $totalLessons) * 80 : 80;
            $quizProgress = $quizPassed ? 20 : 0;
            $this->progress_percent = (int) round($lessonProgress + $quizProgress);
        } else {
            $this->progress_percent = $totalLessons > 0
                ? (int) round(($completedLessons / $totalLessons) * 100)
                : 0;
        }

        // Update status
        if ($this->progress_percent >= 100) {
            $this->status = 'completed';
            $this->completed_at = $this->completed_at ?? now();
        } elseif ($this->progress_percent > 0) {
            $this->status = 'in_progress';
            $this->started_at = $this->started_at ?? now();
        }

        $this->save();
    }
}
