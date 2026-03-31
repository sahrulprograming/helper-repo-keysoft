<?php

namespace Keysoft\HelperLibrary\Http\Middleware;

use Keysoft\HelperLibrary\Jobs\InsertLogActivity;
use Keysoft\HelperLibrary\Support\RequestLogContext;
use Keysoft\HelperLibrary\Support\StructuredLogger;
use Closure;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Log\LogManager;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class LogRequestLifecycle
{
    public function handle(Request $request, Closure $next): Response
    {
        $this->resetLogContext();

        $requestId = $request->headers->get('X-Request-Id', (string) Str::uuid());
        $request->attributes->set('request_id', $requestId);
        $request->attributes->set('_request_started_at', microtime(true));

        app('log')->shareContext(['request_id' => $requestId]);

        try {
            $response = $next($request);
        } catch (Throwable $exception) {
            $request->attributes->set(
                '_request_error',
                $this->extractExceptionMetadata($exception)
            );

            throw $exception;
        }

        $response->headers->set('X-Request-Id', $requestId);

        return $response;
    }

    public function terminate(Request $request, Response $response): void
    {
        $startedAt  = $request->attributes->get('_request_started_at', microtime(true));
        $durationMs = round((microtime(true) - (float) $startedAt) * 1000, 2);

        $context = RequestLogContext::filter(array_merge(
            RequestLogContext::fromRequest($request),
            [
                'method'           => $request->getMethod(),
                'path'             => $request->getPathInfo(),
                'route_name'       => $request->route()?->getName(),
                'route_action'     => $request->route()?->getActionName(),
                'status_code'      => $response->getStatusCode(),
                'duration_ms'      => $durationMs,
                'ip_address'       => $request->ip(),
                'user_agent'       => $request->userAgent(),
                'request_json'     => $this->extractRequestJson($request),
                'response_message' => $this->extractResponseMessage($response),
                'response_json'    => $this->decodeJsonResponse($response),
            ]
        ));

        StructuredLogger::access('http_request', $context, $this->resolveLevel($response->getStatusCode()));

        if (in_array($response->getStatusCode(), [401, 403], true)) {
            StructuredLogger::security(
                $response->getStatusCode() === 401 ? 'unauthorized_request' : 'forbidden_request',
                $context,
                'warning'
            );
        }

        $attributes = $this->buildActivityAttributes($request, $response, $context);

        // Job uses sync connection and runs in the same process inside terminate(),
        // which is called after the response is already sent to the client.
        // Wrapped in try-catch so any log failure does not kill the worker.
        try {
            InsertLogActivity::dispatch($attributes);
        } catch (Throwable) {
            // log insert failure must not propagate after response is sent
        }

        $this->resetLogContext();
    }

    private function buildActivityAttributes(Request $request, Response $response, array $context): array
    {
        return RequestLogContext::filter([
            'user_id'       => $context['user_id'] ?? null,
            'actor_type'    => $this->resolveActorType($context),
            'method'        => $context['method'] ?? $request->getMethod(),
            'path'          => $context['path'] ?? $request->getPathInfo(),
            'response_code' => $context['status_code'] ?? null,
            'ip_address'    => $context['ip_address'] ?? $request->ip(),
            'ref_entity'    => $this->resolveRefEntity($context['request_json'] ?? null),
            'ref_id'        => $this->resolveRefId($context['request_json'] ?? null),
            'tenant_id'     => $context['tenant_id'] ?? null,
            'tenant_code'   => $context['tenant_code'] ?? null,
            'tenant_name'   => $context['tenant_name'] ?? null,
            'package_name'  => $this->resolvePackageName(),
            'action'        => $this->resolveAction($request),
            'payload'       => $context['request_json'] ?? null,
            'response'      => $context['response_json'] ?? null,
            'metadata'      => RequestLogContext::filter([
                'request_id'       => $context['request_id'] ?? null,
                'route_name'       => $context['route_name'] ?? null,
                'route_action'     => $context['route_action'] ?? null,
                'response_message' => $context['response_message'] ?? null,
                'duration_ms'      => $context['duration_ms'] ?? null,
                'user_agent'       => $context['user_agent'] ?? null,
                'auth_context'     => $context['auth_context'] ?? null,
                'device_id'        => $context['device_id'] ?? null,
                'error'            => $this->resolveErrorMetadata($request, $response),
                'category'         => 'access',
                'event'            => 'http_request',
            ]),
            'latency_ms' => max(0, (int) round((float) ($context['duration_ms'] ?? 0))),
            'created_at' => now(),
        ]);
    }

    private function resolveActorType(array $context): string
    {
        return isset($context['user_id']) ? 'USER' : 'SYSTEM';
    }

    private function extractResponseMessage(Response $response): ?string
    {
        $decoded = $this->decodeJsonResponse($response);
        $message = is_array($decoded) ? ($decoded['message'] ?? null) : null;

        return is_string($message) && $message !== '' ? $message : null;
    }

    private function resolveErrorMetadata(Request $request, Response $response): ?array
    {
        $error = $request->attributes->get('_request_error');

        if (is_array($error) && $error !== []) {
            return $error;
        }

        if ($response->getStatusCode() < 400) {
            return null;
        }

        $message = $this->extractResponseMessage($response);

        return is_string($message) && $message !== '' ? ['message' => $message] : null;
    }

    private function extractExceptionMetadata(Throwable $exception): array
    {
        $frame    = $exception->getTrace()[0] ?? [];
        $class    = $frame['class'] ?? null;
        $function = $frame['function'] ?? null;

        return RequestLogContext::filter([
            'exception_class' => $exception::class,
            'message'         => $exception->getMessage(),
            'file'            => $exception->getFile(),
            'line'            => $exception->getLine(),
            'class'           => is_string($class) ? $class : null,
            'function'        => is_string($function) ? $function : null,
        ]);
    }

    private function decodeJsonResponse(Response $response): mixed
    {
        $contentType = $response->headers->get('Content-Type', '');

        if (! str_contains(strtolower($contentType), 'json')) {
            return null;
        }

        $content = $response->getContent();
        if (! is_string($content) || $content === '') {
            return null;
        }

        try {
            return json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return null;
        }
    }

    private function extractRequestJson(Request $request): mixed
    {
        $payload = $this->buildRequestPayload($request);

        return $payload === [] ? null : $this->sanitizePayload($payload);
    }

    private function buildRequestPayload(Request $request): array
    {
        $input = $request->isJson()
            ? $request->json()->all()
            : $request->request->all();

        if (! is_array($input)) {
            $input = [];
        }

        $files = $this->normalizeFiles($request->allFiles());
        $query = $request->query->all();

        if ($query !== []) {
            $input['_query'] = $query;
        }

        if ($files !== []) {
            $input['_files'] = $files;
        }

        return $input;
    }

    private function normalizeFiles(array $files): array
    {
        $normalized = [];

        foreach ($files as $key => $value) {
            if ($value instanceof UploadedFile) {
                $normalized[$key] = RequestLogContext::filter([
                    'original_name' => $value->getClientOriginalName(),
                    'mime_type'     => $value->getClientMimeType(),
                    'size'          => $value->getSize(),
                ]);

                continue;
            }

            if (is_array($value)) {
                $normalized[$key] = $this->normalizeFiles($value);
            }
        }

        return RequestLogContext::filter($normalized);
    }

    private function sanitizePayload(mixed $value, ?string $key = null): mixed
    {
        if (is_array($value)) {
            $sanitized = [];

            foreach ($value as $childKey => $childValue) {
                $sanitized[$childKey] = $this->sanitizePayload(
                    $childValue,
                    is_string($childKey) ? $childKey : null
                );
            }

            return $sanitized;
        }

        if ($this->isSensitiveKey($key)) {
            return '[REDACTED]';
        }

        return is_string($value) ? Str::limit($value, 2000, '...') : $value;
    }

    private function isSensitiveKey(?string $key): bool
    {
        if ($key === null) {
            return false;
        }

        $key = Str::lower($key);

        foreach (['password', 'token', 'access_token', 'refresh_token', 'authorization', 'secret', 'api_key', 'apikey', 'client_secret'] as $sensitive) {
            if (str_contains($key, $sensitive)) {
                return true;
            }
        }

        return false;
    }

    private function resolveAction(Request $request): ?string
    {
        $routeName = $request->route()?->getName();
        if (is_string($routeName) && $routeName !== '') {
            return Str::limit(Str::afterLast($routeName, '.'), 32, '');
        }

        $segments    = $request->segments();
        $lastSegment = end($segments) ?: null;

        return is_string($lastSegment) && $lastSegment !== ''
            ? Str::limit($lastSegment, 32, '')
            : null;
    }

    private function resolveRefEntity(mixed $payload): ?string
    {
        $header = is_array($payload) ? ($payload['header'] ?? []) : [];
        $value  = is_array($header) ? ($header['entity'] ?? null) : null;

        return is_string($value) && $value !== '' ? Str::limit($value, 255, '') : null;
    }

    private function resolveRefId(mixed $payload): ?int
    {
        $data  = is_array($payload) ? ($payload['data'] ?? []) : [];
        $value = is_array($data) ? ($data['id'] ?? null) : null;

        return is_numeric($value) ? (int) $value : null;
    }

    private function resolvePackageName(): string
    {
        $appName = config('app.name', '');

        return is_string($appName) && $appName !== '' && $appName !== 'Laravel'
            ? Str::limit($appName, 255, '')
            : Str::limit(basename(base_path()), 255, '');
    }

    private function resolveLevel(int $statusCode): string
    {
        return match (true) {
            $statusCode >= 500 => 'error',
            $statusCode >= 400 => 'warning',
            default            => 'info',
        };
    }

    private function resetLogContext(): void
    {
        // NOTE: if this service ever migrates to Laravel Octane, shared log context
        // persists across requests in the same worker process. In that case, replace
        // Log::shareContext() with per-request context scoping to avoid context leaking
        // between concurrent requests.
        /** @var LogManager $log */
        $log = app('log');
        $log->withoutContext();
        $log->flushSharedContext();
    }
}
