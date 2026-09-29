<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assignment extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'course_id',
        'tenant_id',
        'title',
        'description',
        'instructions',
        'due_days_after_enrollment',
        'max_file_size_mb',
        'allowed_file_types',
        'is_mandatory',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_mandatory' => 'boolean',
        'is_active' => 'boolean',
    ];

    // ── Relationships ──

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    // ── Scopes ──

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ── Helpers ──

    public function allowedFileTypesArray(): array
    {
        return array_map('trim', explode(',', $this->allowed_file_types));
    }
}
