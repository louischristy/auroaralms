<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\TotpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesTestUsers;

class TwoFactorAuthTest extends TestCase
{
    use RefreshDatabase, CreatesTestUsers;

    public function test_setup_page_accessible_for_authenticated_user(): void
    {
        $tenant = $this->createTenant();
        $user = $this->createEmployee($tenant);

        $response = $this->actingAs($user)->get(route('two-factor.setup'));
        $response->assertStatus(200);
        $response->assertSee('Two-Factor Authentication');
    }

    public function test_setup_redirects_if_already_enabled(): void
    {
        $tenant = $this->createTenant();
        $user = $this->createEmployee($tenant, [
            'two_factor_enabled' => true,
            'two_factor_secret' => 'test-secret',
        ]);

        $response = $this->actingAs($user)
            ->withSession(['2fa:verified' => true])
            ->get(route('two-factor.setup'));

        $response->assertRedirect(route('profile.edit'));
    }

    public function test_confirm_setup_enables_2fa(): void
    {
        $tenant = $this->createTenant();
        $user = $this->createEmployee($tenant);

        // Mock TotpService to avoid timing issues in CI
        $mock = \Mockery::mock(TotpService::class);
        $mock->shouldReceive('verify')->once()->andReturn(true);
        $mock->shouldReceive('generateRecoveryCodes')->once()->andReturn(['code1', 'code2']);
        $this->app->instance(TotpService::class, $mock);

        $response = $this->actingAs($user)
            ->withSession(['2fa_setup_secret' => 'JBSWY3DPEHPK3PXP'])
            ->post(route('two-factor.confirm'), ['code' => '123456']);

        $response->assertRedirect();
        $user->refresh();
        $this->assertTrue($user->two_factor_enabled);
        $this->assertNotNull($user->two_factor_recovery_codes);
    }

    public function test_confirm_setup_rejects_invalid_code(): void
    {
        $tenant = $this->createTenant();
        $user = $this->createEmployee($tenant);
        $totp = new TotpService();
        $secret = $totp->generateSecret();

        $response = $this->actingAs($user)
            ->withSession(['2fa_setup_secret' => $secret])
            ->post(route('two-factor.confirm'), ['code' => '000000']);

        $response->assertSessionHasErrors('code');
        $user->refresh();
        $this->assertFalse($user->two_factor_enabled);
    }

    public function test_login_with_2fa_redirects_to_challenge(): void
    {
        $tenant = $this->createTenant();
        $user = $this->createEmployee($tenant, [
            'two_factor_enabled' => true,
            'two_factor_secret' => encrypt('test-secret'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('two-factor.challenge'));
        $this->assertGuest();
    }

    public function test_challenge_page_requires_session_user(): void
    {
        $response = $this->get(route('two-factor.challenge'));
        $response->assertRedirect(route('login'));
    }

    public function test_2fa_middleware_redirects_unverified_user(): void
    {
        $tenant = $this->createTenant();
        $user = $this->createEmployee($tenant, [
            'two_factor_enabled' => true,
            'two_factor_secret' => encrypt('test-secret'),
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertRedirect(route('two-factor.challenge'));
    }

    public function test_2fa_middleware_allows_verified_user(): void
    {
        $tenant = $this->createTenant();
        $user = $this->createEmployee($tenant, [
            'two_factor_enabled' => true,
            'two_factor_secret' => encrypt('test-secret'),
        ]);

        $response = $this->actingAs($user)
            ->withSession(['2fa:verified' => true])
            ->get(route('dashboard'));

        $response->assertStatus(200);
    }

    public function test_tenant_requiring_2fa_redirects_to_setup(): void
    {
        $tenant = $this->createTenant(['require_two_factor' => true]);
        $user = $this->createEmployee($tenant, [
            'two_factor_enabled' => false,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertRedirect(route('two-factor.setup'));
    }

    public function test_disable_2fa_requires_password(): void
    {
        $tenant = $this->createTenant();
        $user = $this->createEmployee($tenant, [
            'two_factor_enabled' => true,
            'two_factor_secret' => encrypt('test-secret'),
        ]);

        $response = $this->actingAs($user)
            ->withSession(['2fa:verified' => true])
            ->post(route('two-factor.disable'), [
                'current_password' => 'wrong-password',
            ]);

        $response->assertSessionHasErrors('current_password');
        $user->refresh();
        $this->assertTrue($user->two_factor_enabled);
    }

    public function test_disable_2fa_blocked_when_tenant_requires_it(): void
    {
        $tenant = $this->createTenant(['require_two_factor' => true]);
        $user = $this->createEmployee($tenant, [
            'two_factor_enabled' => true,
            'two_factor_secret' => encrypt('test-secret'),
        ]);

        $response = $this->actingAs($user)
            ->withSession(['2fa:verified' => true])
            ->post(route('two-factor.disable'), [
                'current_password' => 'password',
            ]);

        $user->refresh();
        $this->assertTrue($user->two_factor_enabled);
    }

    private function generateCodeForSecret(string $secret): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $binary = '';
        foreach (str_split(strtoupper($secret)) as $char) {
            $pos = strpos($chars, $char);
            if ($pos !== false) {
                $binary .= str_pad(decbin($pos), 5, '0', STR_PAD_LEFT);
            }
        }
        $secretBytes = '';
        foreach (str_split($binary, 8) as $byte) {
            if (strlen($byte) === 8) {
                $secretBytes .= chr(bindec($byte));
            }
        }

        $counter = (int) floor(time() / 30);
        $counterBytes = pack('J', $counter);
        $hash = hash_hmac('sha1', $counterBytes, $secretBytes, true);
        $offset = ord($hash[19]) & 0x0F;
        $code = ((ord($hash[$offset]) & 0x7F) << 24) |
                ((ord($hash[$offset + 1]) & 0xFF) << 16) |
                ((ord($hash[$offset + 2]) & 0xFF) << 8) |
                (ord($hash[$offset + 3]) & 0xFF);
        $otp = $code % (10 ** 6);

        return str_pad((string) $otp, 6, '0', STR_PAD_LEFT);
    }
}
