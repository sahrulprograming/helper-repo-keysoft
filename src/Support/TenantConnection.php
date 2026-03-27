<?php

namespace Keysoft\HelperLibrary\Support;

use InvalidArgumentException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Keysoft\HelperLibrary\Dto\ActiveTenant;
use Keysoft\HelperLibrary\Models\Central\MsTenantCentral;
use RuntimeException;

class TenantConnection
{
    protected static bool $initialized = false;
    protected static ?string $currentTenant = null;

    public static function set(): void
    {
        $tenant = ActiveTenant::fromSession();

        if (! $tenant) {
            return;
        }

        self::setForTenant($tenant);
    }

    public static function setForTenant(ActiveTenant $tenant): void
    {
        self::setByTenantCode($tenant->code);
    }

    public static function setByTenantCode(string $tenantCode): void
    {
        $tenantCode = trim($tenantCode);

        if ($tenantCode === '') {
            throw new InvalidArgumentException('Tenant code tidak boleh kosong.');
        }

        $model = self::findTenantModelByCode($tenantCode);

        if (! $model) {
            throw new RuntimeException("Tenant [{$tenantCode}] tidak ditemukan.");
        }

        self::applyConnection($model);
    }

    public static function clear(): void
    {
        self::$currentTenant = null;
        self::$initialized = false;

        DB::disconnect('tenant');
        DB::purge('tenant');
    }

    public static function currentTenantCode(): ?string
    {
        return self::$currentTenant;
    }

    protected static function findTenantModelByCode(string $tenantCode): ?MsTenantCentral
    {
        return MsTenantCentral::query()
            ->where('code', $tenantCode)
            ->first();
    }

    protected static function applyConnection(MsTenantCentral $model): void
    {
        // prevent re-init same tenant
        if (self::$initialized && self::$currentTenant === $model->code) {
            return;
        }

        self::$currentTenant = (string) $model->code;
        self::$initialized = true;

        $connection = [
            'driver' => 'pgsql',
            'host' => $model->db_host,
            'port' => (int) $model->db_port,
            'database' => $model->db_name,
            'username' => $model->db_user,
            'password' => $model->db_password,
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'public',
            'sslmode' => 'prefer',
        ];

        DB::disconnect('tenant');
        Config::set('database.connections.tenant', $connection);

        // purge connection to force fresh
        DB::purge('tenant');
        DB::reconnect('tenant');
    }
}
