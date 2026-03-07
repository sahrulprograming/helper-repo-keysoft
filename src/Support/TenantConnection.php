<?php

namespace Keysoft\HelperLibrary\Support;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Keysoft\HelperLibrary\Dto\ActiveTenant;
use Keysoft\HelperLibrary\Models\Central\MsTenantCentral;

class TenantConnection
{
    protected static bool $initialized = false;
    protected static ?string $currentTenant = null;

    public static function set(): void
    {
        $tenant = ActiveTenant::fromSession();

        if (!$tenant) {
            return;
        }

        // prevent re-init same tenant
        if (self::$initialized && self::$currentTenant === $tenant->code) {
            return;
        }

        $model = MsTenantCentral::where('code', $tenant->code)->first();

        if (!$model) {
            return;
        }

        self::$currentTenant = $tenant->code;
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