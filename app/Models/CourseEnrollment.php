<?php

namespace App\Models;

use App\Notifications\CourseAssigned;
use App\Notifications\CourseCompleted;
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

    protected static function booted(): void
    {
        static::created(function (CourseEnrollment $enrollment) {
            try {
                $enrollment->load(['user', 'course']);
                if ($enrollment->user) {
                    $enrollment->user->notify(new CourseAssigned($enrollment->course, $enrollment));
                }
            } catch (\Throwable $e) {
                \Log::warning('Failed to send course assigned notification: ' . $e->getMessage());
            }
        });
    }

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
        $wasCompleted = $this->getOriginal('status') === 'completed';

        if ($this->progress_percent >= 100) {
            $this->status = 'completed';
            $this->completed_at = $this->completed_at ?? now();
        } elseif ($this->progress_percent > 0) {
            $this->status = 'in_progress';
            $this->started_at = $this->started_at ?? now();
        }

        $this->save();

        // Auto-issue certificate and notify on first completion
        if ($this->status === 'completed' && !$wasCompleted) {
            $this->issueCertificate();
            $this->sendCompletionNotification();

            // Evaluate badges on completion
            app(\App\Services\BadgeService::class)->evaluate($this->user);
        }
    }

    protected function issueCertificate(): void
    {
        $existing = Certificate::where('user_id', $this->user_id)
            ->where('course_id', $this->course_id)
            ->where('tenant_id', $this->tenant_id)
            ->first();

        if ($existing) {
            return;
        }

        // Get best quiz score if quiz exists
        $score = QuizAttempt::where('user_id', $this->user_id)
            ->where('course_id', $this->course_id)
            ->where('passed', true)
            ->max('score');

        Certificate::create([
            'user_id' => $this->user_id,
            'course_id' => $this->course_id,
            'tenant_id' => $this->tenant_id,
            'enrollment_id' => $this->id,
            'certificate_number' => Certificate::generateNumber(),
            'score' => $score,
            'issued_at' => now(),
            'expires_at' => now()->addYear(),
        ]);
    }

    protected function sendCompletionNotification(): void
    {
        try {
            $this->load(['user', 'course']);
            $certificate = Certificate::where('user_id', $this->user_id)
                ->where('course_id', $this->course_id)
                ->where('tenant_id', $this->tenant_id)
                ->first();

            if ($this->user) {
                $this->user->notify(new CourseCompleted($this->course, $certificate));
            }
        } catch (\Throwable $e) {
            \Log::warning('Failed to send course completed notification: ' . $e->getMessage());
        }
    }
}
