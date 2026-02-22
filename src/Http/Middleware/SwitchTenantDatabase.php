<?php

namespace Keysoft\HelperLibrary\Http\Middleware;

use Keysoft\HelperLibrary\Dto\ActiveTenant;
use Keysoft\HelperLibrary\Models\MsTenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class SwitchTenantDatabase
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        // Cek apakah Super Admin sudah memilih tenant di session
        $tenant = ActiveTenant::fromSession();

        if ($tenant) {
            $tenant = MsTenant::where('code', $tenant->code)->first();

            if ($tenant) {
                // 1. Put full config
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

                DB::disconnect('tenant');
                DB::purge('tenant');

                // 3. Recreate connection fresh
                DB::reconnect('tenant');

            }
        }

        return $next($request);
    }
}
