<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'department_id',
        'name',
        'email',
        'password',
        'job_title',
        'employee_id',
        'phone',
        'avatar_path',
        'is_active',
        'last_login_at',
        'must_change_password',
        'activated_at',
        'two_factor_secret',
        'two_factor_enabled',
        'two_factor_recovery_codes',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
        'must_change_password' => 'boolean',
        'activated_at' => 'datetime',
        'two_factor_secret' => 'encrypted',
        'two_factor_enabled' => 'boolean',
        'two_factor_recovery_codes' => 'encrypted:array',
    ];

    // ── Role helpers ──

    public function isPlatformAdmin(): bool
    {
        return $this->hasRole('platform-admin');
    }

    public function isClientAdmin(): bool
    {
        return $this->hasRole('client-admin');
    }

    public function isManager(): bool
    {
        return $this->hasRole('manager');
    }

    public function isEmployee(): bool
    {
        return $this->hasRole('employee');
    }

    /**
     * Platform admins are not tenant-scoped.
     */
    public function isTenantScoped(): bool
    {
        return !$this->isPlatformAdmin();
    }

    // ── Relationships ──

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function managedDepartments(): HasMany
    {
        return $this->hasMany(Department::class, 'manager_id');
    }

    public function courseEnrollments(): HasMany
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    public function policyAcknowledgments(): HasMany
    {
        return $this->hasMany(PolicyAcknowledgment::class);
    }

    // ── Scopes ──

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
