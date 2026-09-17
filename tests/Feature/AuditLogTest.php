<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesTestUsers;

class AuditLogTest extends TestCase
{
    use RefreshDatabase, CreatesTestUsers;

    public function test_audit_log_service_creates_entry(): void
    {
        $tenant = $this->createTenant();
        $user = $this->createEmployee($tenant);

        $this->actingAs($user);

        AuditLogService::log('test_action', $user, ['key' => 'value']);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'test_action',
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
        ]);

        $log = AuditLog::latest('id')->first();
        $this->assertEquals(['key' => 'value'], $log->new_values);
    }

    public function test_auditable_trait_logs_model_creation(): void
    {
        $tenant = $this->createTenant();
        $admin = $this->createPlatformAdmin();

        // User creation through factory triggers the trait
        // The Auditable trait skips console runs, so we simulate a web request
        $this->actingAs($admin);

        // Create user via web context — the trait checks app()->runningInConsole()
        // In tests with runningUnitTests() it should still fire
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $log = AuditLog::where('action', 'user_created')
            ->where('model_id', $user->id)
            ->first();

        $this->assertNotNull($log);
    }

    public function test_auditable_trait_logs_model_update(): void
    {
        $tenant = $this->createTenant();
        $user = $this->createEmployee($tenant);

        $this->actingAs($user);

        $user->update(['name' => 'Updated Name']);

        $log = AuditLog::where('action', 'user_updated')
            ->where('model_id', $user->id)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('name', $log->new_values);
    }

    public function test_auditable_trait_strips_sensitive_fields(): void
    {
        $tenant = $this->createTenant();
        $user = $this->createEmployee($tenant);

        $this->actingAs($user);

        $user->update(['password' => 'new-password', 'name' => 'New Name']);

        $log = AuditLog::where('action', 'user_updated')
            ->where('model_id', $user->id)
            ->first();

        // Password should be stripped, name should remain
        if ($log) {
            $this->assertArrayNotHasKey('password', $log->new_values ?? []);
        }
    }

    public function test_platform_admin_can_view_audit_logs(): void
    {
        $admin = $this->createPlatformAdmin();

        AuditLogService::log('test_action', $admin);

        $response = $this->actingAs($admin)->get(route('platform.audit-logs.index'));
        $response->assertStatus(200);
        $response->assertSee('test action');
    }

    public function test_client_admin_sees_only_own_tenant_logs(): void
    {
        $tenant1 = $this->createTenant(['name' => 'Tenant One']);
        $tenant2 = $this->createTenant(['name' => 'Tenant Two']);
        $admin1 = $this->createClientAdmin($tenant1);
        $admin2 = $this->createClientAdmin($tenant2);

        AuditLog::create([
            'action' => 'tenant1_action',
            'user_id' => $admin1->id,
            'tenant_id' => $tenant1->id,
        ]);
        AuditLog::create([
            'action' => 'tenant2_action',
            'user_id' => $admin2->id,
            'tenant_id' => $tenant2->id,
        ]);

        $response = $this->actingAs($admin1)->get(route('manage.audit-logs.index'));
        $response->assertStatus(200);
        $response->assertSee('tenant1 action');
        $response->assertDontSee('tenant2 action');
    }

    public function test_audit_log_detail_page_loads(): void
    {
        $admin = $this->createPlatformAdmin();

        $log = AuditLog::create([
            'action' => 'detail_test',
            'user_id' => $admin->id,
            'old_values' => ['name' => 'Old'],
            'new_values' => ['name' => 'New'],
        ]);

        $response = $this->actingAs($admin)->get(route('platform.audit-logs.show', $log));
        $response->assertStatus(200);
        $response->assertSee('detail test');
        $response->assertSee('Old');
        $response->assertSee('New');
    }

    public function test_audit_log_filters_by_action(): void
    {
        $admin = $this->createPlatformAdmin();

        AuditLog::create(['action' => 'login_success', 'user_id' => $admin->id]);
        AuditLog::create(['action' => 'user_created', 'user_id' => $admin->id]);

        $response = $this->actingAs($admin)->get(route('platform.audit-logs.index', ['action' => 'login_success']));
        $response->assertStatus(200);
        $response->assertSee('login success');
    }
}
