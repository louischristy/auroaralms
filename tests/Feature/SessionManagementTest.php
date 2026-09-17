<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Tests\Traits\CreatesTestUsers;

class SessionManagementTest extends TestCase
{
    use RefreshDatabase, CreatesTestUsers;

    public function test_sessions_page_loads(): void
    {
        $tenant = $this->createTenant();
        $user = $this->createEmployee($tenant);

        $response = $this->actingAs($user)
            ->get(route('sessions.index'));

        $response->assertStatus(200);
        $response->assertSee('Active Sessions');
    }

    public function test_profile_page_links_to_sessions(): void
    {
        $tenant = $this->createTenant();
        $user = $this->createEmployee($tenant);

        $response = $this->actingAs($user)
            ->get(route('profile.edit'));

        $response->assertStatus(200);
        $response->assertSee('Active Sessions');
        $response->assertSee(route('sessions.index'));
    }

    public function test_revoke_session_requires_password(): void
    {
        $tenant = $this->createTenant();
        $user = $this->createEmployee($tenant);

        $response = $this->actingAs($user)
            ->delete(route('sessions.destroy', 'fake-session-id'), [
                'current_password' => 'wrong-password',
            ]);

        $response->assertSessionHasErrors('current_password');
    }

    public function test_cannot_revoke_current_session(): void
    {
        $tenant = $this->createTenant();
        $user = $this->createEmployee($tenant);

        // Login to get a real session
        $this->actingAs($user);
        $sessionId = session()->getId();

        $response = $this->delete(route('sessions.destroy', $sessionId), [
            'current_password' => 'password',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_force_logout_requires_admin_role(): void
    {
        $tenant = $this->createTenant();
        $employee = $this->createEmployee($tenant);
        $otherUser = $this->createEmployee($tenant);

        $response = $this->actingAs($employee)
            ->post(route('manage.users.force-logout', $otherUser->id));

        $response->assertStatus(403);
    }

    public function test_platform_admin_can_force_logout_user(): void
    {
        $admin = $this->createPlatformAdmin();
        $tenant = $this->createTenant();
        $user = $this->createEmployee($tenant);

        // Insert a fake session for the target user
        DB::table('sessions')->insert([
            'id' => 'target-session-id',
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Agent',
            'payload' => '',
            'last_activity' => time(),
        ]);

        $response = $this->actingAs($admin)
            ->post(route('platform.users.force-logout', $user->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('sessions', ['id' => 'target-session-id']);
    }

    public function test_client_admin_cannot_force_logout_other_tenant_user(): void
    {
        $tenant1 = $this->createTenant();
        $tenant2 = $this->createTenant();
        $admin = $this->createClientAdmin($tenant1);
        $otherUser = $this->createEmployee($tenant2);

        $response = $this->actingAs($admin)
            ->post(route('manage.users.force-logout', $otherUser->id));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }
}
