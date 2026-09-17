<?php

namespace App\Services;

/**
 * TOTP (Time-Based One-Time Password) implementation per RFC 6238.
 * No external packages required — uses PHP's hash_hmac.
 */
class TotpService
{
    /** Time step in seconds (standard: 30). */
    private const TIME_STEP = 30;

    /** Number of digits in the OTP. */
    private const DIGITS = 6;

    /** Base32 alphabet for encoding/decoding secrets. */
    private const BASE32_CHARS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /**
     * Generate a random secret key (160-bit, base32-encoded).
     */
    public function generateSecret(): string
    {
        $bytes = random_bytes(20); // 160 bits
        return $this->base32Encode($bytes);
    }

    /**
     * Generate the otpauth:// URI for QR code scanning.
     */
    public function getQrUri(string $secret, string $email, string $issuer = 'Auroara LMS'): string
    {
        $issuer = rawurlencode($issuer);
        $label = rawurlencode("{$issuer}:{$email}");

        return "otpauth://totp/{$label}?secret={$secret}&issuer={$issuer}&digits=" . self::DIGITS . '&period=' . self::TIME_STEP;
    }

    /**
     * Verify a TOTP code. Allows ±1 time-step window for clock drift.
     */
    public function verify(string $secret, string $code): bool
    {
        $code = str_pad(trim($code), self::DIGITS, '0', STR_PAD_LEFT);
        $secretBytes = $this->base32Decode($secret);
        $currentTimeStep = (int) floor(time() / self::TIME_STEP);

        // Check current and ±1 time window
        for ($offset = -1; $offset <= 1; $offset++) {
            $generated = $this->generateCode($secretBytes, $currentTimeStep + $offset);
            if (hash_equals($generated, $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate a TOTP code for a given time counter.
     */
    private function generateCode(string $secretBytes, int $counter): string
    {
        // Pack counter as 8-byte big-endian
        $counterBytes = pack('J', $counter); // 64-bit unsigned big-endian

        // HMAC-SHA1
        $hash = hash_hmac('sha1', $counterBytes, $secretBytes, true);

        // Dynamic truncation (RFC 4226 §5.4)
        $offset = ord($hash[19]) & 0x0F;
        $binary =
            ((ord($hash[$offset]) & 0x7F) << 24) |
            ((ord($hash[$offset + 1]) & 0xFF) << 16) |
            ((ord($hash[$offset + 2]) & 0xFF) << 8) |
            (ord($hash[$offset + 3]) & 0xFF);

        $otp = $binary % (10 ** self::DIGITS);

        return str_pad((string) $otp, self::DIGITS, '0', STR_PAD_LEFT);
    }

    /**
     * Generate recovery codes (8 codes, 10 chars each).
     */
    public function generateRecoveryCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = strtoupper(substr(bin2hex(random_bytes(5)), 0, 10));
        }
        return $codes;
    }

    /**
     * Base32 encode binary data.
     */
    private function base32Encode(string $data): string
    {
        $binary = '';
        foreach (str_split($data) as $char) {
            $binary .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
        }

        $result = '';
        $chunks = str_split($binary, 5);
        foreach ($chunks as $chunk) {
            $chunk = str_pad($chunk, 5, '0', STR_PAD_RIGHT);
            $result .= self::BASE32_CHARS[bindec($chunk)];
        }

        return $result;
    }

    /**
     * Base32 decode to binary.
     */
    private function base32Decode(string $data): string
    {
        $data = strtoupper(rtrim($data, '='));
        $binary = '';

        foreach (str_split($data) as $char) {
            $pos = strpos(self::BASE32_CHARS, $char);
            if ($pos === false) {
                continue;
            }
            $binary .= str_pad(decbin($pos), 5, '0', STR_PAD_LEFT);
        }

        $result = '';
        $bytes = str_split($binary, 8);
        foreach ($bytes as $byte) {
            if (strlen($byte) === 8) {
                $result .= chr(bindec($byte));
            }
        }

        return $result;
    }
}
