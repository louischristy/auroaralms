<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoTenantSeeder extends Seeder
{
    public function run(): void
    {
        // ── Platform Admin (no tenant) ──
        $platformAdmin = User::firstOrCreate(
            ['email' => 'admin@auroara.com', 'tenant_id' => null],
            [
                'name' => 'Platform Admin',
                'password' => Hash::make('AuroaraAdmin@2024'),
                'is_active' => true,
                'email_verified_at' => now(),
                'activated_at' => now(),
            ]
        );
        $platformAdmin->assignRole('platform-admin');

        // ── Demo Tenant ──
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'demo-company'],
            [
                'name' => 'Demo Company Sdn Bhd',
                'is_active' => true,
                'primary_color' => '#2B4C7E',
                'accent_color' => '#5BC0EB',
                'max_users' => 50,
                'subscription_plan' => 'standard',
                'settings' => json_encode([
                    'secondary_color' => '#3A7BD5',
                    'welcome_message' => 'Welcome to your Security Awareness Training portal.',
                ]),
            ]
        );

        // Departments
        $itDept = Department::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Information Technology'],
            ['description' => 'IT & Infrastructure']
        );
        $hrDept = Department::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Human Resources'],
            ['description' => 'HR & People Operations']
        );

        // Client Admin
        $clientAdmin = User::firstOrCreate(
            ['email' => 'admin@democompany.com', 'tenant_id' => $tenant->id],
            [
                'name' => 'Client Admin',
                'password' => Hash::make('DemoAdmin@2024'),
                'department_id' => $itDept->id,
                'job_title' => 'IT Manager',
                'is_active' => true,
                'email_verified_at' => now(),
                'activated_at' => now(),
            ]
        );
        $clientAdmin->assignRole('client-admin');

        // Manager
        $manager = User::firstOrCreate(
            ['email' => 'manager@democompany.com', 'tenant_id' => $tenant->id],
            [
                'name' => 'John Manager',
                'password' => Hash::make('DemoManager@2024'),
                'department_id' => $itDept->id,
                'job_title' => 'Team Lead',
                'is_active' => true,
                'email_verified_at' => now(),
                'activated_at' => now(),
            ]
        );
        $manager->assignRole('manager');

        // Set department manager
        $itDept->update(['manager_id' => $manager->id]);

        // Employees
        $employees = [
            ['name' => 'Sarah Employee', 'email' => 'sarah@democompany.com', 'department_id' => $itDept->id, 'job_title' => 'Developer'],
            ['name' => 'Ali Employee', 'email' => 'ali@democompany.com', 'department_id' => $itDept->id, 'job_title' => 'Support Engineer'],
            ['name' => 'Mei Ling', 'email' => 'meiling@democompany.com', 'department_id' => $hrDept->id, 'job_title' => 'HR Executive'],
        ];

        foreach ($employees as $emp) {
            $user = User::firstOrCreate(
                ['email' => $emp['email'], 'tenant_id' => $tenant->id],
                [
                    'name' => $emp['name'],
                    'password' => Hash::make('DemoUser@2024'),
                    'department_id' => $emp['department_id'],
                    'job_title' => $emp['job_title'],
                    'is_active' => true,
                    'email_verified_at' => now(),
                    'activated_at' => now(),
                ]
            );
            $user->assignRole('employee');
        }
    }
}
