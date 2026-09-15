<?php

namespace App\Traits;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Apply to any model that is scoped to a tenant.
 * Automatically filters queries to the current tenant
 * and sets tenant_id on creation.
 */
trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        // Auto-scope all queries to current tenant
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (app()->bound('current_tenant_id') && $tenantId = app('current_tenant_id')) {
                $builder->where($builder->getModel()->getTable() . '.tenant_id', $tenantId);
            }
        });

        // Auto-set tenant_id on creation
        static::creating(function ($model) {
            if (!$model->tenant_id && app()->bound('current_tenant_id') && $tenantId = app('current_tenant_id')) {
                $model->tenant_id = $tenantId;
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Query without tenant scoping (for platform admin).
     */
    public function scopeWithoutTenantScope(Builder $builder): Builder
    {
        return $builder->withoutGlobalScope('tenant');
    }

    /**
     * Query for a specific tenant.
     */
    public function scopeForTenant(Builder $builder, int $tenantId): Builder
    {
        return $builder->withoutGlobalScope('tenant')
            ->where($this->getTable() . '.tenant_id', $tenantId);
    }
}
