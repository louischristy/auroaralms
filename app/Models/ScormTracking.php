<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScormTracking extends Model
{
    protected $table = 'scorm_tracking';

    protected $fillable = [
        'user_id', 'lesson_id', 'course_id', 'tenant_id',
        'lesson_status', 'lesson_location', 'score_raw', 'score_min', 'score_max',
        'total_time_seconds', 'session_time_seconds', 'suspend_data',
        'exit_type', 'entry_type', 'cmi_data',
    ];

    protected $casts = [
        'score_raw'  => 'decimal:2',
        'score_min'  => 'decimal:2',
        'score_max'  => 'decimal:2',
        'cmi_data'   => 'array',
    ];

    public function user()    { return $this->belongsTo(User::class); }
    public function lesson()  { return $this->belongsTo(Lesson::class); }
    public function course()  { return $this->belongsTo(Course::class); }

    /**
     * Check if the learner has completed or passed this SCORM lesson.
     */
    public function isComplete(): bool
    {
        return in_array($this->lesson_status, ['completed', 'passed']);
    }

    /**
     * Parse SCORM time format (HH:MM:SS or HHHH:MM:SS.SS) to seconds.
     */
    public static function parseScormTime(string $time): int
    {
        if (preg_match('/^(\d+):(\d{2}):(\d{2}(?:\.\d+)?)$/', $time, $m)) {
            return (int) $m[1] * 3600 + (int) $m[2] * 60 + (int) round((float) $m[3]);
        }
        return 0;
    }
}
