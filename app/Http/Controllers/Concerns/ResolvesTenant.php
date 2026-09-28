<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;

/**
 * Helpers for admin controllers that serve both platform admins
 * (no tenant, must pick one) and client admins (locked to own tenant).
 */
trait ResolvesTenant
{
    protected function isPlatformAdmin(): bool
    {
        return Auth::user()->hasRole('platform-admin');
    }

    /** Tenant list for the platform-admin tenant picker; null for tenant users. */
    protected function tenantOptions()
    {
        return $this->isPlatformAdmin() ? Tenant::orderBy('name')->get() : null;
    }

    /**
     * Tenant that a new record should belong to. Platform admins must supply
     * one; everyone else is locked to their own tenant.
     */
    protected function resolveTenantId(?int $requested): ?int
    {
        return $this->isPlatformAdmin() ? $requested : Auth::user()->tenant_id;
    }
}
