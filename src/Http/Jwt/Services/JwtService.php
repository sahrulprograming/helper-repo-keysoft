<?php

namespace Keysoft\HelperLibrary\Http\Jwt\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Keysoft\HelperLibrary\Http\Jwt\Exceptions\JwtException;

class JwtService
{
    private string $secret;
    private string $algorithm;
    private int $ttl;
    private int $refreshTtl;
    private bool $blacklist;

    public function __construct()
    {
        $this->secret = Config::get('keysoft-lib-config.jwt.secret');
        $this->algorithm = Config::get('keysoft-lib-config.jwt.algorithm', 'HS256');
        $this->ttl = Config::get('keysoft-lib-config.jwt.ttl', 3600);
        $this->refreshTtl = Config::get('keysoft-lib-config.jwt.refresh_ttl', 86400);
        $this->blacklist = Config::get('keysoft-lib-config.jwt.blacklist_enabled', true);
    }

    /**
     * Generate JWT Token (Multi Device + Permission)
     */
    public function generate(array $payload, string $deviceId = null): string
    {
        $now = time();

        $payload = array_merge($payload, [
            'iat' => $now,
            'exp' => $now + $this->ttl,
            'device_id' => $deviceId ?? uniqid('device_', true)
        ]);

        return JWT::encode($payload, $this->secret, $this->algorithm);
    }

    /**
     * Validate Token
     */
    public function validate(string $token): array
    {
        $this->checkBlacklist($token);

        try {
            $decoded = JWT::decode($token, new Key($this->secret, $this->algorithm));

            return (array) $this->normalizeClaims($decoded);
        } catch (ExpiredException) {
            throw new JwtException('Token expired');
        } catch (SignatureInvalidException) {
            throw new JwtException('Invalid token signature');
        } catch (\Exception) {
            throw new JwtException('Invalid token');
        }
    }

    /**
     * Refresh Token (Rotation)
     */
    public function refresh(string $token): string
    {
        $payload = $this->validate($token);

        if (!$this->canRefresh($payload)) {
            throw new JwtException('Refresh token expired');
        }

        // rotation: blacklist old token
        $this->blacklist($token);

        return $this->generate($payload, $payload['device_id'] ?? null);
    }

    /**
     * Blacklist token (revoke)
     */
    public function blacklist(string $token): void
    {
        if (!$this->blacklist) {
            return;
        }

        $exp = $this->getExpiration($token);

        if ($exp) {
            $ttl = $exp - time();

            if ($ttl > 0) {
                Cache::put($this->getBlacklistKey($token), true, $ttl);
            }
        }
    }

    /**
     * Check blacklist
     */
    protected function checkBlacklist(string $token): void
    {
        if (!$this->blacklist) {
            return;
        }

        if (Cache::has($this->getBlacklistKey($token))) {
            throw new JwtException('Token revoked');
        }
    }

    /**
     * Refresh TTL check
     */
    protected function canRefresh(array $payload): bool
    {
        if (!isset($payload['iat'])) {
            return false;
        }

        return (time() - $payload['iat']) <= $this->refreshTtl;
    }

    /**
     * Get token expiration
     */
    protected function getExpiration(string $token): ?int
    {
        try {
            $payload = JWT::decode($token, new Key($this->secret, $this->algorithm));
            return $payload->exp ?? null;
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * Blacklist key (per device)
     */
    protected function getBlacklistKey(string $token): string
    {
        try {
            $payload = JWT::decode($token, new Key($this->secret, $this->algorithm));

            $deviceId = $payload->device_id ?? 'unknown';

            return 'jwt_blacklist_' . md5($deviceId);
        } catch (\Exception) {
            return 'jwt_blacklist_' . md5($token);
        }
    }

    /**
     * Permissions
     */
    public function getPermissions(string $token): array
    {
        $payload = $this->validate($token);

        return $payload['permissions'] ?? [];
    }

    public function hasPermission(string $token, string $permission): bool
    {
        return in_array($permission, $this->getPermissions($token));
    }

    protected function normalizeClaims(mixed $value): mixed
    {
        if (is_object($value)) {
            $value = (array) $value;
        }

        if (!is_array($value)) {
            return $value;
        }

        foreach ($value as $key => $item) {
            $value[$key] = $this->normalizeClaims($item);
        }

        return $value;
    }
}
