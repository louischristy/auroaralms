<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class SubscriptionPlan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'billing_cycle',
        'base_price',
        'price_per_user',
        'currency',
        'min_users',
        'max_users',
        'max_courses',
        'max_storage_gb',
        'features',
        'modules',
        'custom_branding',
        'custom_domain',
        'api_access',
        'sso_enabled',
        'priority_support',
        'is_active',
        'is_featured',
        'sort_order',
        'trial_days',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'price_per_user' => 'decimal:2',
        'features' => 'array',
        'modules' => 'array',
        'custom_branding' => 'boolean',
        'custom_domain' => 'boolean',
        'api_access' => 'boolean',
        'sso_enabled' => 'boolean',
        'priority_support' => 'boolean',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (SubscriptionPlan $plan) {
            if (empty($plan->slug) && !empty($plan->name)) {
                $plan->slug = Str::slug($plan->name);
            }
        });
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(TenantSubscription::class, 'plan_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function calculatePrice(int $userCount): float
    {
        return (float) $this->base_price + ((float) $this->price_per_user * $userCount);
    }
}
