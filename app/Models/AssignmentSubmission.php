<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class AssignmentSubmission extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'pending';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_UNDER_REVIEW = 'under_review';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_RESUBMITTED = 'resubmitted';

    protected $fillable = [
        'assignment_id',
        'user_id',
        'file_path',
        'file_name',
        'file_size',
        'notes',
        'status',
        'reviewer_id',
        'reviewer_feedback',
        'reviewed_at',
        'grade',
        'submitted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    // ── Relationships ──

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    // ── Scopes ──

    public function scopePendingReview(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_SUBMITTED, self::STATUS_RESUBMITTED]);
    }

    // ── Helpers ──

    public function isReviewable(): bool
    {
        return in_array($this->status, [self::STATUS_SUBMITTED, self::STATUS_RESUBMITTED]);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Pending',
            self::STATUS_SUBMITTED => 'Submitted',
            self::STATUS_UNDER_REVIEW => 'Under Review',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_RESUBMITTED => 'Resubmitted',
            default => ucfirst($this->status),
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            self::STATUS_APPROVED => 'bg-green-100 text-green-700',
            self::STATUS_REJECTED => 'bg-red-100 text-red-700',
            self::STATUS_SUBMITTED, self::STATUS_RESUBMITTED => 'bg-yellow-100 text-yellow-700',
            self::STATUS_UNDER_REVIEW => 'bg-blue-100 text-blue-700',
            default => 'bg-gray-100 text-gray-500',
        };
    }
}
