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
        'certificate_number', 'verification_code', 'is_public',
        'score', 'issued_at', 'expires_at',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_public' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Certificate $certificate) {
            if (empty($certificate->verification_code)) {
                $certificate->verification_code = static::generateVerificationCode();
            }
        });
    }

    public static function generateNumber(): string
    {
        return 'CERT-' . strtoupper(Str::random(4)) . '-' . date('Ymd') . '-' . strtoupper(Str::random(4));
    }

    public static function generateVerificationCode(): string
    {
        do {
            $code = 'AUR-' . strtoupper(Str::random(4)) . '-' . strtoupper(Str::random(4));
        } while (static::where('verification_code', $code)->exists());

        return $code;
    }

    public static function findByVerificationCode(string $code): ?self
    {
        return static::where('verification_code', $code)->first();
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
