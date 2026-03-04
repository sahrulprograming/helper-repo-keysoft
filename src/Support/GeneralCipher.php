<?php

namespace Keysoft\HelperLibrary\Support;

use Illuminate\Support\Str;
use RuntimeException;

class GeneralCipher
{
    protected string $cipher;
    protected string $prefix;
    protected string $key;

    public function __construct(
        ?string $key = null,
        string $prefix = 'enc1',
        string $cipher = 'AES-256-CBC'
    ) {
        $this->cipher = $cipher;
        $this->prefix = $prefix;
        $this->key = $this->resolveKey($key);
    }

    public function encrypt(?string $plainText): ?string
    {
        if ($plainText === null || $plainText === '') {
            return $plainText;
        }

        if ($this->isEncrypted($plainText)) {
            return $plainText;
        }

        $ivLength = openssl_cipher_iv_length($this->cipher);
        $iv = random_bytes($ivLength);

        $cipherText = openssl_encrypt(
            $plainText,
            $this->cipher,
            $this->key,
            OPENSSL_RAW_DATA,
            $iv
        );

        if ($cipherText === false) {
            throw new RuntimeException('Encryption failed.');
        }

        $ivBase64 = base64_encode($iv);
        $cipherTextBase64 = base64_encode($cipherText);

        $macBase64 = base64_encode(
            hash_hmac('sha256', "{$ivBase64}.{$cipherTextBase64}", $this->key, true)
        );

        return implode(':', [
            $this->prefix,
            $ivBase64,
            $cipherTextBase64,
            $macBase64
        ]);
    }

    public function decrypt(?string $payload): ?string
    {
        if ($payload === null || $payload === '') {
            return $payload;
        }

        if (! $this->isEncrypted($payload)) {
            return $payload;
        }

        $parts = explode(':', $payload, 4);

        if (count($parts) !== 4) {
            throw new RuntimeException('Invalid encrypted payload format.');
        }

        [, $ivBase64, $cipherTextBase64, $macBase64] = $parts;

        $iv = base64_decode($ivBase64, true);
        $cipherText = base64_decode($cipherTextBase64, true);
        $mac = base64_decode($macBase64, true);

        if (($iv === false) || ($cipherText === false) || ($mac === false)) {
            throw new RuntimeException('Invalid encrypted payload.');
        }

        $expectedMac = hash_hmac(
            'sha256',
            "{$ivBase64}.{$cipherTextBase64}",
            $this->key,
            true
        );

        if (! hash_equals($expectedMac, $mac)) {
            throw new RuntimeException('Invalid MAC signature.');
        }

        $plainText = openssl_decrypt(
            $cipherText,
            $this->cipher,
            $this->key,
            OPENSSL_RAW_DATA,
            $iv
        );

        if ($plainText === false) {
            throw new RuntimeException('Decryption failed.');
        }

        return $plainText;
    }

    public function isEncrypted(?string $value): bool
    {
        return is_string($value)
            && Str::startsWith($value, $this->prefix . ':');
    }

    protected function resolveKey(?string $configuredKey): string
    {
        $configuredKey = $configuredKey ?? config('app.key');

        if (! is_string($configuredKey) || trim($configuredKey) === '') {
            throw new RuntimeException('Encryption key is required.');
        }

        $configuredKey = trim($configuredKey);

        if (Str::startsWith($configuredKey, 'base64:')) {
            $decoded = base64_decode(Str::after($configuredKey, 'base64:'), true);

            if (($decoded === false) || (strlen($decoded) !== 32)) {
                throw new RuntimeException('Base64 key must decode to 32 bytes.');
            }

            return $decoded;
        }

        if (preg_match('/\A[0-9a-fA-F]{64}\z/', $configuredKey) === 1) {
            $decoded = hex2bin($configuredKey);

            if (($decoded === false) || (strlen($decoded) !== 32)) {
                throw new RuntimeException('Hex key must be 32 bytes.');
            }

            return $decoded;
        }

        throw new RuntimeException(
            'Key must use format base64:... or 64-character hex.'
        );
    }
}