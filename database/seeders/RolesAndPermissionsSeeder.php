<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ── Permissions ──

        $permissions = [
            // Tenant management (platform only)
            'tenants.view',
            'tenants.create',
            'tenants.edit',
            'tenants.delete',

            // User management
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'users.import',

            // Department management
            'departments.view',
            'departments.create',
            'departments.edit',
            'departments.delete',

            // Course management
            'courses.view',
            'courses.create',
            'courses.edit',
            'courses.delete',
            'courses.assign',
            'courses.push',

            // Policy/document management
            'policies.view',
            'policies.create',
            'policies.edit',
            'policies.delete',
            'policies.push',
            'policies.acknowledge',

            // Reports
            'reports.view_own',
            'reports.view_team',
            'reports.view_tenant',
            'reports.view_all',
            'reports.export',

            // Settings
            'settings.platform',
            'settings.tenant',

            // Phishing simulations
            'phishing.manage',
            'phishing.view_results',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // ── Roles ──

        // Platform Admin (Auroara) — full access
        $platformAdmin = Role::firstOrCreate(['name' => 'platform-admin']);
        $platformAdmin->givePermissionTo(Permission::all());

        // Client Admin — tenant-level management
        $clientAdmin = Role::firstOrCreate(['name' => 'client-admin']);
        $clientAdmin->givePermissionTo([
            'users.view', 'users.create', 'users.edit', 'users.delete', 'users.import',
            'departments.view', 'departments.create', 'departments.edit', 'departments.delete',
            'courses.view', 'courses.assign',
            'policies.view', 'policies.create', 'policies.edit', 'policies.delete', 'policies.push',
            'reports.view_tenant', 'reports.export',
            'settings.tenant',
            'phishing.view_results',
        ]);

        // Manager — department-level oversight
        $manager = Role::firstOrCreate(['name' => 'manager']);
        $manager->givePermissionTo([
            'users.view',
            'departments.view',
            'courses.view', 'courses.assign',
            'policies.view',
            'reports.view_team',
        ]);

        // Employee — learning participant
        $employee = Role::firstOrCreate(['name' => 'employee']);
        $employee->givePermissionTo([
            'courses.view',
            'policies.view', 'policies.acknowledge',
            'reports.view_own',
        ]);
    }
}
