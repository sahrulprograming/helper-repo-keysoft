<?php

namespace Keysoft\HelperLibrary\Support;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use RuntimeException;

class GeneralCipher
{
    protected const DEFAULT_CIPHER = 'aes-256-gcm';
    protected const LEGACY_CIPHER = 'AES-256-CBC';
    protected const GCM_TAG_LENGTH = 16;
    protected const LEGACY_MAC_LENGTH = 32;

    protected string $cipher;
    protected string $prefix;
    protected string $key;

    public function __construct(
        ?string $key = null,
        ?string $prefix = null,
        ?string $cipher = null
    ) {
        $this->cipher = $this->resolveCipher(
            $cipher ?? Config::get('keysoft-lib-config.general_cipher.cipher')
        );
        $this->prefix = $this->resolvePrefix(
            $prefix ?? Config::get('keysoft-lib-config.general_cipher.prefix')
        );
        $this->key = $this->resolveKey(
            $key ?? Config::get('keysoft-lib-config.general_cipher.key')
        );
    }

    public function encrypt(?string $plainText): ?string
    {
        if ($plainText === null || $plainText === '') {
            return $plainText;
        }

        if ($this->isEncrypted($plainText)) {
            return $plainText;
        }

        if ($this->cipher === self::LEGACY_CIPHER) {
            return $this->encryptLegacy($plainText);
        }

        $ivLength = openssl_cipher_iv_length($this->cipher);
        $iv = random_bytes($ivLength);
        $tag = '';

        $cipherText = openssl_encrypt(
            $plainText,
            $this->cipher,
            $this->key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            '',
            self::GCM_TAG_LENGTH
        );

        if ($cipherText === false || strlen($tag) !== self::GCM_TAG_LENGTH) {
            throw new RuntimeException('Encryption failed.');
        }

        return implode(':', [
            $this->prefix,
            base64_encode($iv),
            base64_encode($cipherText),
            base64_encode($tag)
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

        [, $ivBase64, $cipherTextBase64, $authBase64] = $parts;

        $iv = base64_decode($ivBase64, true);
        $cipherText = base64_decode($cipherTextBase64, true);
        $auth = base64_decode($authBase64, true);

        if (($iv === false) || ($cipherText === false) || ($auth === false)) {
            throw new RuntimeException('Invalid encrypted payload.');
        }

        if (strlen($auth) === self::GCM_TAG_LENGTH) {
            return $this->decryptAes($iv, $cipherText, $auth);
        }

        if (strlen($auth) === self::LEGACY_MAC_LENGTH) {
            return $this->decryptLegacy(
                $iv,
                $cipherText,
                $auth,
                $ivBase64,
                $cipherTextBase64
            );
        }

        throw new RuntimeException('Invalid encrypted payload.');
    }

    public function isEncrypted(?string $value): bool
    {
        return is_string($value)
            && Str::startsWith($value, $this->prefix . ':');
    }

    protected function encryptLegacy(string $plainText): string
    {
        $ivLength = openssl_cipher_iv_length(self::LEGACY_CIPHER);
        $iv = random_bytes($ivLength);

        $cipherText = openssl_encrypt(
            $plainText,
            self::LEGACY_CIPHER,
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

    protected function decryptAes(string $iv, string $cipherText, string $tag): string
    {
        $this->assertIvLength($iv, self::DEFAULT_CIPHER);

        $plainText = openssl_decrypt(
            $cipherText,
            self::DEFAULT_CIPHER,
            $this->key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        if ($plainText === false) {
            throw new RuntimeException('Decryption failed.');
        }

        return $plainText;
    }

    protected function decryptLegacy(
        string $iv,
        string $cipherText,
        string $mac,
        string $ivBase64,
        string $cipherTextBase64
    ): string {
        $this->assertIvLength($iv, self::LEGACY_CIPHER);

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
            self::LEGACY_CIPHER,
            $this->key,
            OPENSSL_RAW_DATA,
            $iv
        );

        if ($plainText === false) {
            throw new RuntimeException('Decryption failed.');
        }

        return $plainText;
    }

    protected function assertIvLength(string $iv, string $cipher): void
    {
        $expectedLength = openssl_cipher_iv_length($cipher);

        if ($expectedLength <= 0 || strlen($iv) !== $expectedLength) {
            throw new RuntimeException('Invalid IV length.');
        }
    }

    protected function resolvePrefix(?string $configuredPrefix): string
    {
        $configuredPrefix = is_string($configuredPrefix)
            ? trim($configuredPrefix)
            : '';

        if ($configuredPrefix === '') {
            throw new RuntimeException('Encryption prefix is required.');
        }

        if (str_contains($configuredPrefix, ':')) {
            throw new RuntimeException('Encryption prefix cannot contain colon.');
        }

        return $configuredPrefix;
    }

    protected function resolveCipher(?string $configuredCipher): string
    {
        $configuredCipher = is_string($configuredCipher)
            ? trim($configuredCipher)
            : '';

        if ($configuredCipher === '') {
            return self::DEFAULT_CIPHER;
        }

        return match (strtolower($configuredCipher)) {
            'aes-256-gcm' => self::DEFAULT_CIPHER,
            'aes-256-cbc' => self::LEGACY_CIPHER,
            default => throw new RuntimeException(
                'Cipher must be AES-256-GCM or AES-256-CBC.'
            ),
        };
    }

    protected function resolveKey(?string $configuredKey): string
    {
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
