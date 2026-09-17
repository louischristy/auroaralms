<?php

namespace Tests\Unit;

use App\Services\TotpService;
use Tests\TestCase;

class TotpServiceTest extends TestCase
{
    private TotpService $totp;

    protected function setUp(): void
    {
        parent::setUp();
        $this->totp = new TotpService();
    }

    public function test_generates_base32_secret(): void
    {
        $secret = $this->totp->generateSecret();

        $this->assertNotEmpty($secret);
        // 160-bit = 20 bytes = 32 base32 chars
        $this->assertEquals(32, strlen($secret));
        // Only valid base32 characters
        $this->assertMatchesRegularExpression('/^[A-Z2-7]+$/', $secret);
    }

    public function test_generates_unique_secrets(): void
    {
        $secrets = array_map(fn() => $this->totp->generateSecret(), range(1, 10));
        $this->assertCount(10, array_unique($secrets));
    }

    public function test_generates_valid_qr_uri(): void
    {
        $secret = $this->totp->generateSecret();
        $uri = $this->totp->getQrUri($secret, 'test@example.com', 'TestApp');

        $this->assertStringStartsWith('otpauth://totp/', $uri);
        $this->assertStringContainsString("secret={$secret}", $uri);
        $this->assertStringContainsString('issuer=TestApp', $uri);
        $this->assertStringContainsString('digits=6', $uri);
        $this->assertStringContainsString('period=30', $uri);
        $this->assertStringContainsString('test%40example.com', $uri);
    }

    public function test_verify_accepts_valid_code(): void
    {
        $secret = $this->totp->generateSecret();

        // Generate a code for the current time step using the same algorithm
        $code = $this->generateTestCode($secret);

        $this->assertTrue($this->totp->verify($secret, $code));
    }

    public function test_verify_rejects_invalid_code(): void
    {
        $secret = $this->totp->generateSecret();
        $this->assertFalse($this->totp->verify($secret, '000000'));
    }

    public function test_verify_pads_short_codes(): void
    {
        $secret = $this->totp->generateSecret();
        // Should not crash on short input
        $this->assertFalse($this->totp->verify($secret, '123'));
    }

    public function test_generates_recovery_codes(): void
    {
        $codes = $this->totp->generateRecoveryCodes();

        $this->assertCount(8, $codes);
        foreach ($codes as $code) {
            $this->assertEquals(10, strlen($code));
            $this->assertMatchesRegularExpression('/^[A-F0-9]+$/', $code);
        }
    }

    public function test_recovery_codes_are_unique(): void
    {
        $codes = $this->totp->generateRecoveryCodes();
        $this->assertCount(8, array_unique($codes));
    }

    /**
     * Generate a valid TOTP code for testing using the same algorithm.
     */
    private function generateTestCode(string $secret): string
    {
        // Decode the base32 secret
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
