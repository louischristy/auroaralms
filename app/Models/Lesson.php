<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use App\Traits\Auditable;

class Lesson extends Model
{
    use Auditable;

    protected $fillable = [
        'course_id', 'title', 'slug', 'content', 'content_type',
        'video_url', 'duration_minutes', 'sort_order', 'is_active',
        'scorm_version', 'scorm_entry_point', 'scorm_package_path',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function ($lesson) {
            if (empty($lesson->slug)) {
                $lesson->slug = Str::slug($lesson->title);
            }
        });
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function completions(): HasMany
    {
        return $this->hasMany(LessonCompletion::class);
    }

    public function isCompletedBy(int $userId): bool
    {
        return $this->completions()->where('user_id', $userId)->exists();
    }
}
