<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;

class Tenant extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'name',
        'slug',
        'domain',
        'logo_path',
        'favicon_path',
        'primary_color',
        'accent_color',
        'is_active',
        'require_two_factor',
        'settings',
        'max_users',
        'subscription_plan',
        'subscription_expires_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'require_two_factor' => 'boolean',
        'settings' => 'array',
        'subscription_expires_at' => 'datetime',
    ];

    /**
     * Get the tenant's brand settings with defaults.
     */
    public function getBrandSettings(): array
    {
        $defaults = [
            'company_name' => $this->name,
            'primary_color' => '#2B4C7E',
            'accent_color' => '#5BC0EB',
            'show_powered_by' => true,
        ];

        return array_merge($defaults, $this->settings['brand'] ?? []);
    }

    // ── Relationships ──

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    // ── Scopes ──

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
