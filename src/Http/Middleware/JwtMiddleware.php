<?php

namespace Keysoft\HelperLibrary\Http\Middleware;

use Illuminate\Support\Facades\Log;
use Keysoft\HelperLibrary\Dto\ActiveTenant;
use Keysoft\HelperLibrary\Http\Jwt\Exceptions\JwtException;
use Keysoft\HelperLibrary\Http\Jwt\Services\JwtService;
use Keysoft\HelperLibrary\Http\Utils\ResponseFormatter;

class JwtMiddleware
{
    public function handle($request, \Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return ResponseFormatter::error('Token missing', 401)->toResponse();
        }

        try {
            $jwt = new JwtService();
            $payload = $jwt->validate($token);
            $request->attributes->set('jwt_payload', $payload);
            $request->attributes->set('jwt_permissions', $payload['extra']['permissions'] ?? []);

            if ($activeTenant = ActiveTenant::fromPayload($payload)) {
                $activeTenant->toSession();
                $request->attributes->set('active_tenant', $activeTenant->toArray());
            }else{
                return ResponseFormatter::error("Invalid extra data token", 401, $payload)->toResponse();
            }
        } catch (JwtException $e) {
            return ResponseFormatter::error($e->getMessage(), 401)->toResponse();
        } catch (\Throwable $e) {
            // 🔥 Log unexpected error
            Log::error('Unexpected JWT middleware error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Jangan bocorkan detail error ke client
            return ResponseFormatter::error(
                'Authentication service unavailable',
                500
            )->toResponse();
        }

        return $next($request);
    }
}
