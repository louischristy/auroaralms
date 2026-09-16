<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Course extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title', 'slug', 'description', 'objectives', 'category',
        'difficulty', 'thumbnail_path', 'duration_minutes', 'passing_score',
        'is_active', 'is_mandatory', 'sort_order',
    ];

    protected $casts = [
        'objectives' => 'array',
        'is_active' => 'boolean',
        'is_mandatory' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function ($course) {
            if (empty($course->slug)) {
                $course->slug = Str::slug($course->title);
            }
        });
    }

    // ── Relationships ──

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class)->orderBy('sort_order');
    }

    public function quiz(): HasOne
    {
        return $this->hasOne(Quiz::class);
    }

    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'course_tenant')
            ->withPivot('is_mandatory', 'due_date')
            ->withTimestamps();
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    // ── Scopes ──

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    // ── Helpers ──

    public function totalLessons(): int
    {
        return $this->lessons()->count();
    }

    public function enrollmentForUser(int $userId): ?CourseEnrollment
    {
        return $this->enrollments()->where('user_id', $userId)->first();
    }
}
