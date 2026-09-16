<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quiz extends Model
{
    protected $fillable = [
        'course_id', 'title', 'instructions', 'time_limit_minutes',
        'max_attempts', 'shuffle_questions', 'show_correct_answers', 'is_active',
    ];

    protected $casts = [
        'shuffle_questions' => 'boolean',
        'show_correct_answers' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class)->orderBy('sort_order');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function attemptsBy(int $userId): HasMany
    {
        return $this->attempts()->where('user_id', $userId);
    }

    public function remainingAttempts(int $userId): ?int
    {
        if ($this->max_attempts === 0) return null; // unlimited
        return max(0, $this->max_attempts - $this->attemptsBy($userId)->count());
    }
}
