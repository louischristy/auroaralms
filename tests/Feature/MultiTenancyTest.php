<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesTestUsers;

class MultiTenancyTest extends TestCase
{
    use RefreshDatabase, CreatesTestUsers;

    public function test_platform_admin_can_access_platform_routes(): void
    {
        $admin = $this->createPlatformAdmin();

        $response = $this->actingAs($admin)->get(route('platform.tenants.index'));
        $response->assertStatus(200);
    }

    public function test_employee_cannot_access_platform_routes(): void
    {
        $tenant = $this->createTenant();
        $user = $this->createEmployee($tenant);

        $response = $this->actingAs($user)->get(route('platform.tenants.index'));
        $response->assertStatus(403);
    }

    public function test_client_admin_can_access_manage_routes(): void
    {
        $tenant = $this->createTenant();
        $admin = $this->createClientAdmin($tenant);

        $response = $this->actingAs($admin)->get(route('manage.users.index'));
        $response->assertStatus(200);
    }

    public function test_employee_cannot_access_manage_routes(): void
    {
        $tenant = $this->createTenant();
        $user = $this->createEmployee($tenant);

        $response = $this->actingAs($user)->get(route('manage.users.index'));
        $response->assertStatus(403);
    }

    public function test_inactive_tenant_blocks_login(): void
    {
        $tenant = $this->createTenant(['is_active' => false]);
        $user = $this->createEmployee($tenant);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
    }

    public function test_profile_page_accessible_to_authenticated_user(): void
    {
        $tenant = $this->createTenant();
        $user = $this->createEmployee($tenant);

        $response = $this->actingAs($user)->get(route('profile.edit'));
        $response->assertStatus(200);
        $response->assertSee($user->name);
    }

    public function test_password_change_works(): void
    {
        $tenant = $this->createTenant();
        $user = $this->createEmployee($tenant);

        $response = $this->actingAs($user)->put(route('profile.password'), [
            'current_password' => 'password',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_password_change_requires_current_password(): void
    {
        $tenant = $this->createTenant();
        $user = $this->createEmployee($tenant);

        $response = $this->actingAs($user)->put(route('profile.password'), [
            'current_password' => 'wrong-password',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertSessionHasErrors('current_password');
    }
}
