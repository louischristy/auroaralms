<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LearningPathEnrollment extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'user_id', 'learning_path_id', 'tenant_id', 'status',
        'progress_percent', 'started_at', 'completed_at', 'due_date',
    ];

    protected $casts = [
        'progress_percent' => 'decimal:2',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'due_date' => 'datetime',
    ];

    // ── Relationships ── (tenant() is provided by BelongsToTenant)

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function learningPath(): BelongsTo
    {
        return $this->belongsTo(LearningPath::class);
    }

    // ── Helpers ──

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isOverdue(): bool
    {
        return $this->due_date !== null
            && $this->due_date->isPast()
            && !$this->isCompleted();
    }

    /**
     * Recalculate progress from the user's completed course enrollments
     * versus the total courses in the path.
     */
    public function recalculateProgress(): void
    {
        $courseIds = LearningPathCourse::where('learning_path_id', $this->learning_path_id)
            ->pluck('course_id');

        $total = $courseIds->count();

        $completed = $total === 0 ? 0 : CourseEnrollment::where('user_id', $this->user_id)
            ->whereIn('course_id', $courseIds)
            ->where('status', 'completed')
            ->count();

        $percent = $total > 0 ? round(($completed / $total) * 100, 2) : 0;

        $this->progress_percent = $percent;

        if ($total > 0 && $completed >= $total) {
            $this->status = 'completed';
            $this->completed_at = $this->completed_at ?? now();
        } elseif ($completed > 0 || $this->status === 'in_progress') {
            $this->status = 'in_progress';
            $this->completed_at = null;
        }

        if ($this->status === 'in_progress' && !$this->started_at) {
            $this->started_at = now();
        }

        $this->save();
    }
}
