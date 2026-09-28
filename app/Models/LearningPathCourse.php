<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class LearningPathCourse extends Pivot
{
    protected $table = 'learning_path_courses';

    public $incrementing = true;

    protected $fillable = [
        'learning_path_id', 'course_id', 'sort_order', 'is_required', 'unlock_after_days',
    ];

    protected $casts = [
        'is_required' => 'boolean',
    ];

    public function learningPath(): BelongsTo
    {
        return $this->belongsTo(LearningPath::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
