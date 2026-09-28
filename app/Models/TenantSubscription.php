<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TenantSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'plan_id',
        'status',
        'custom_price',
        'custom_price_per_user',
        'discount_percent',
        'discount_reason',
        'billing_cycle',
        'currency',
        'custom_features',
        'custom_modules',
        'custom_max_users',
        'custom_max_courses',
        'custom_max_storage_gb',
        'starts_at',
        'expires_at',
        'trial_ends_at',
        'cancelled_at',
        'cancellation_reason',
        'notes',
    ];

    protected $casts = [
        'custom_price' => 'decimal:2',
        'custom_price_per_user' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'custom_features' => 'array',
        'custom_modules' => 'array',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(SubscriptionInvoice::class, 'subscription_id');
    }

    // Scopes

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeTrial(Builder $query): Builder
    {
        return $query->where('status', 'trial');
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->where('status', 'expired')
                ->orWhere(function (Builder $q2) {
                    $q2->whereNotNull('expires_at')->where('expires_at', '<', now());
                });
        });
    }

    // State helpers

    public function isActive(): bool
    {
        return $this->status === 'active' && !$this->isExpired();
    }

    public function isTrial(): bool
    {
        return $this->status === 'trial'
            && (!$this->trial_ends_at || $this->trial_ends_at->isFuture());
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired'
            || ($this->expires_at !== null && $this->expires_at->isPast());
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled' || $this->cancelled_at !== null;
    }

    // Pricing helpers

    protected function applyDiscount(float $amount): float
    {
        $discount = (float) ($this->discount_percent ?? 0);

        if ($discount <= 0) {
            return $amount;
        }

        return $amount * (1 - min($discount, 100) / 100);
    }

    public function getEffectivePrice(): float
    {
        $price = $this->custom_price !== null
            ? (float) $this->custom_price
            : (float) ($this->plan?->base_price ?? 0);

        return round($this->applyDiscount($price), 2);
    }

    public function getEffectivePricePerUser(): float
    {
        $price = $this->custom_price_per_user !== null
            ? (float) $this->custom_price_per_user
            : (float) ($this->plan?->price_per_user ?? 0);

        return round($this->applyDiscount($price), 2);
    }

    public function getEffectiveMaxUsers(): ?int
    {
        return $this->custom_max_users ?? $this->plan?->max_users;
    }

    public function getEffectiveMaxCourses(): ?int
    {
        return $this->custom_max_courses ?? $this->plan?->max_courses;
    }

    public function getEffectiveMaxStorageGb(): ?int
    {
        return $this->custom_max_storage_gb ?? $this->plan?->max_storage_gb;
    }

    public function getEffectiveFeatures(): array
    {
        return $this->mergeOverrides($this->plan?->features ?? [], $this->custom_features ?? []);
    }

    public function getEffectiveModules(): array
    {
        return $this->mergeOverrides($this->plan?->modules ?? [], $this->custom_modules ?? []);
    }

    /**
     * Merge plan values with custom overrides. Associative arrays are
     * overridden key by key; plain lists are combined without duplicates.
     */
    protected function mergeOverrides(array $base, array $overrides): array
    {
        if (array_is_list($base) && array_is_list($overrides)) {
            return array_values(array_unique(array_merge($base, $overrides), SORT_REGULAR));
        }

        return array_merge($base, $overrides);
    }

    /**
     * Full monthly total with overrides and discount applied.
     */
    public function calculateMonthlyTotal(int $userCount): float
    {
        $base = $this->custom_price !== null
            ? (float) $this->custom_price
            : (float) ($this->plan?->base_price ?? 0);

        $perUser = $this->custom_price_per_user !== null
            ? (float) $this->custom_price_per_user
            : (float) ($this->plan?->price_per_user ?? 0);

        $maxUsers = $this->getEffectiveMaxUsers();
        $billableUsers = $maxUsers ? min($userCount, $maxUsers) : $userCount;
        $minUsers = (int) ($this->plan?->min_users ?? 0);
        $billableUsers = max($billableUsers, $minUsers);

        $subtotal = $base + ($perUser * $billableUsers);

        return round($this->applyDiscount($subtotal), 2);
    }
}
