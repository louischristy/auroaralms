<?php

namespace Tests\Traits;

use App\Models\Tenant;
use App\Models\User;
use Spatie\Permission\Models\Role;

trait CreatesTestUsers
{
    protected function seedRoles(): void
    {
        Role::firstOrCreate(['name' => 'platform-admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'client-admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'employee', 'guard_name' => 'web']);
    }

    protected function createPlatformAdmin(array $attrs = []): User
    {
        $this->seedRoles();
        $user = User::factory()->create(array_merge([
            'tenant_id' => null,
        ], $attrs));
        $user->assignRole('platform-admin');
        return $user;
    }

    protected function createTenant(array $attrs = []): Tenant
    {
        return Tenant::create(array_merge([
            'name' => 'Test Tenant',
            'slug' => 'test-tenant-' . uniqid(),
            'is_active' => true,
        ], $attrs));
    }

    protected function createClientAdmin(Tenant $tenant, array $attrs = []): User
    {
        $this->seedRoles();
        $user = User::factory()->create(array_merge([
            'tenant_id' => $tenant->id,
        ], $attrs));
        $user->assignRole('client-admin');
        return $user;
    }

    protected function createEmployee(Tenant $tenant, array $attrs = []): User
    {
        $this->seedRoles();
        $user = User::factory()->create(array_merge([
            'tenant_id' => $tenant->id,
        ], $attrs));
        $user->assignRole('employee');
        return $user;
    }
}
