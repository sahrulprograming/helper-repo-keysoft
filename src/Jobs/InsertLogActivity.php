<?php

namespace Keysoft\HelperLibrary\Jobs;

use Keysoft\HelperLibrary\Models\Tenant\LogActivity;
use Keysoft\HelperLibrary\Support\RequestLogContext;
use Keysoft\HelperLibrary\Support\TenantConnection;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class InsertLogActivity implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    // Log activity must not be retried — duplicate entries are worse than missing ones.
    public int $tries = 1;

    public function __construct(private readonly array $attributes)
    {
        // Use sync connection so the job runs in the same process after the response
        // is sent (inside terminate()). The background driver spawns a new PHP process
        // which loses the active session/request context and fails silently.
        $this->onConnection('sync');
    }

    public function handle(): void
    {
        $tenantCode = $this->resolveTenantCode();

        if ($tenantCode === null) {
            Log::warning('log_activity_skipped_missing_tenant_code', [
                'actor_type'  => $this->attributes['actor_type'] ?? null,
                'path'        => $this->attributes['path'] ?? null,
                'tenant_id'   => $this->attributes['tenant_id'] ?? null,
                'tenant_name' => $this->attributes['tenant_name'] ?? null,
                'request_id'  => $this->attributes['metadata']['request_id'] ?? null,
            ]);

            return;
        }

        try {
            TenantConnection::setByTenantCode($tenantCode);

            $activity             = new LogActivity();
            $activity->timestamps = false;
            $activity->fill([
                'user_id'       => $this->attributes['user_id'] ?? null,
                'actor_type'    => $this->attributes['actor_type'] ?? 'SYSTEM',
                'method'        => $this->attributes['method'] ?? null,
                'path'          => $this->attributes['path'] ?? null,
                'response_code' => $this->attributes['response_code'] ?? null,
                'ip_address'    => $this->attributes['ip_address'] ?? null,
                'ref_entity'    => $this->attributes['ref_entity'] ?? null,
                'ref_id'        => $this->attributes['ref_id'] ?? null,
                'tenant_id'     => $this->attributes['tenant_id'] ?? null,
                'tenant_code'   => $tenantCode,
                'tenant_name'   => $this->attributes['tenant_name'] ?? null,
                'package_name'  => $this->attributes['package_name'] ?? null,
                'action'        => $this->attributes['action'] ?? null,
                'payload'       => $this->attributes['payload'] ?? null,
                'response'      => $this->attributes['response'] ?? null,
                'metadata'      => $this->resolveMetadata(),
                'latency_ms'    => max(0, (int) ($this->attributes['latency_ms'] ?? 0)),
                'created_at'    => $this->attributes['created_at'] ?? now(),
            ]);
            $activity->save();
        } catch (Throwable $exception) {
            Log::error('log_activity_insert_failed', [
                'message'     => $exception->getMessage(),
                'actor_type'  => $this->attributes['actor_type'] ?? null,
                'path'        => $this->attributes['path'] ?? null,
                'tenant_code' => $tenantCode,
                'request_id'  => $this->attributes['metadata']['request_id'] ?? null,
            ]);
        } finally {
            TenantConnection::clear();
        }
    }

    private function resolveTenantCode(): ?string
    {
        $code = is_string($this->attributes['tenant_code'] ?? null)
            ? trim($this->attributes['tenant_code'])
            : '';

        return $code !== '' ? $code : null;
    }

    private function resolveMetadata(): ?array
    {
        $metadata = is_array($this->attributes['metadata'] ?? null)
            ? $this->attributes['metadata']
            : [];

        if (! array_key_exists('error', $metadata)) {
            $error = $this->extractErrorMetadata();

            if ($error !== null) {
                $metadata['error'] = $error;
            }
        }

        $metadata = RequestLogContext::filter($metadata);

        return $metadata !== [] ? $metadata : null;
    }

    private function extractErrorMetadata(): ?array
    {
        if ((int) ($this->attributes['response_code'] ?? 0) < 400) {
            return null;
        }

        $message = is_array($this->attributes['response'] ?? null)
            ? ($this->attributes['response']['message'] ?? null)
            : null;

        return is_string($message) && $message !== '' ? ['message' => $message] : null;
    }
}
