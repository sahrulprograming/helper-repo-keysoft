<?php

namespace Keysoft\HelperLibrary\Support;

use Illuminate\Http\Request;

class RequestLogContext
{
    public static function fromRequest(Request $request): array
    {
        $payload      = $request->attributes->get('jwt_payload');
        $activeTenant = $request->attributes->get('active_tenant');

        $payload      = is_array($payload) ? $payload : [];
        $activeTenant = is_array($activeTenant) ? $activeTenant : [];

        $tenant = $payload['extra']['tenant'] ?? $activeTenant;
        $tenant = is_array($tenant) ? $tenant : [];

        return self::filter([
            'request_id'   => $request->attributes->get('request_id'),
            'user_id'      => $payload['userId'] ?? null,
            'auth_context' => $payload['ctx'] ?? null,
            'device_id'    => $payload['deviceId'] ?? null,
            'tenant_code'  => $payload['selectedTenantCode'] ?? ($tenant['code'] ?? null),
            'tenant_id'    => $tenant['id'] ?? ($activeTenant['id'] ?? null),
            'tenant_name'  => $tenant['name'] ?? ($activeTenant['name'] ?? null),
        ]);
    }

    public static function filter(array $context): array
    {
        return array_filter(
            $context,
            static fn (mixed $value): bool => $value !== null && $value !== '' && $value !== []
        );
    }
}
