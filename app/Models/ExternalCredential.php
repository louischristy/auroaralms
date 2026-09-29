<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExternalCredential extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'user_id',
        'tenant_id',
        'platform',
        'credential_name',
        'credential_url',
        'credential_id',
        'issuer',
        'issued_at',
        'expires_at',
        'certificate_file',
        'status',
        'notes',
    ];

    protected $casts = [
        'issued_at' => 'date',
        'expires_at' => 'date',
    ];

    // Status constants
    const STATUS_ACTIVE = 'active';
    const STATUS_EXPIRED = 'expired';
    const STATUS_REVOKED = 'revoked';
    const STATUS_PENDING = 'pending_verification';

    const STATUSES = [
        self::STATUS_ACTIVE => 'Active',
        self::STATUS_EXPIRED => 'Expired',
        self::STATUS_REVOKED => 'Revoked',
        self::STATUS_PENDING => 'Pending Verification',
    ];

    // Platform constants
    const PLATFORM_COURSERA = 'coursera';
    const PLATFORM_UDEMY = 'udemy';
    const PLATFORM_LINKEDIN = 'linkedin_learning';
    const PLATFORM_GOOGLE = 'google';
    const PLATFORM_AWS = 'aws';
    const PLATFORM_MICROSOFT = 'microsoft';
    const PLATFORM_OTHER = 'other';

    const PLATFORMS = [
        self::PLATFORM_COURSERA => ['name' => 'Coursera', 'color' => '#0056D2'],
        self::PLATFORM_UDEMY => ['name' => 'Udemy', 'color' => '#A435F0'],
        self::PLATFORM_LINKEDIN => ['name' => 'LinkedIn Learning', 'color' => '#0A66C2'],
        self::PLATFORM_GOOGLE => ['name' => 'Google', 'color' => '#4285F4'],
        self::PLATFORM_AWS => ['name' => 'AWS', 'color' => '#FF9900'],
        self::PLATFORM_MICROSOFT => ['name' => 'Microsoft', 'color' => '#00A4EF'],
        self::PLATFORM_OTHER => ['name' => 'Other', 'color' => '#6B7280'],
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeExpired($query)
    {
        return $query->where('status', self::STATUS_EXPIRED);
    }

    public function isExpired(): bool
    {
        if ($this->status === self::STATUS_EXPIRED) {
            return true;
        }

        return $this->expires_at && $this->expires_at->isPast();
    }

    public function getPlatformNameAttribute(): string
    {
        return self::PLATFORMS[$this->platform]['name'] ?? ucfirst($this->platform);
    }

    public function getPlatformColorAttribute(): string
    {
        return self::PLATFORMS[$this->platform]['color'] ?? '#6B7280';
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }
}
