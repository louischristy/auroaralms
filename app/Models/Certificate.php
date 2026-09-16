<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Certificate extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'user_id', 'course_id', 'tenant_id', 'enrollment_id',
        'certificate_number', 'score', 'issued_at', 'expires_at',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public static function generateNumber(): string
    {
        return 'CERT-' . strtoupper(Str::random(4)) . '-' . date('Ymd') . '-' . strtoupper(Str::random(4));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(CourseEnrollment::class, 'enrollment_id');
    }
}
