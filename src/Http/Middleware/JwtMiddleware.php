<?php

namespace Keysoft\HelperLibrary\Http\Middleware;

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
            $request->attributes->set('jwt_permissions', $payload['permissions'] ?? []);
        } catch (JwtException $e) {
            return ResponseFormatter::error($e->getMessage(), 401)->toResponse();
        }

        return $next($request);
    }
}