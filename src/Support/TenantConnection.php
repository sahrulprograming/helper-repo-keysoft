<?php
namespace Keysoft\HelperLibrary\Support;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Keysoft\HelperLibrary\Dto\ActiveTenant;
use Keysoft\HelperLibrary\Models\MsTenant;

class TenantConnection
{
    public static function set(): void
    {
        $tenant = ActiveTenant::fromSession();

        if (!$tenant) {
            return;
        }

        $tenant = MsTenant::where('code', $tenant->code)->first();

        if (!$tenant) {
            return;
        }

        Config::set('database.connections.tenant', [
            'driver' => 'pgsql',
            'host' => $tenant->db_host,
            'port' => (int) $tenant->db_port,
            'database' => $tenant->db_name,
            'username' => $tenant->db_user,
            'password' => $tenant->db_password,
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'public',
            'sslmode' => 'prefer',
        ]);

        DB::purge('tenant');
    }
}
