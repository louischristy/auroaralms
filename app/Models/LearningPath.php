<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class LearningPath extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant, Auditable;

    protected $fillable = [
        'tenant_id', 'title', 'slug', 'description', 'thumbnail_path',
        'difficulty', 'estimated_duration_minutes', 'is_sequential',
        'is_active', 'is_mandatory', 'sort_order',
    ];

    protected $casts = [
        'is_sequential' => 'boolean',
        'is_active' => 'boolean',
        'is_mandatory' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function ($path) {
            if (empty($path->slug)) {
                $path->slug = Str::slug($path->title);
            }
        });
    }

    // ── Relationships ── (tenant() is provided by BelongsToTenant)

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'learning_path_courses')
            ->using(LearningPathCourse::class)
            ->withPivot('sort_order', 'is_required', 'unlock_after_days')
            ->withTimestamps()
            ->orderBy('learning_path_courses.sort_order');
    }

    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'learning_path_tenant')->withTimestamps();
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(LearningPathEnrollment::class);
    }

    public function surveys(): HasMany
    {
        return $this->hasMany(Survey::class);
    }

    // ── Scopes ──

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
