<?php

namespace Keysoft\HelperLibrary\Support;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Keysoft\HelperLibrary\Models\MsTenantCentral;
use Keysoft\HelperLibrary\Models\MsUserCentral as CentralMsUser;
use Keysoft\HelperLibrary\Models\Tenant\MsUser as TenantMsUser;

class TenantUserSynchronizer
{
    public function syncByUserId(int|string $userId, array $previousTenantIds = []): void
    {
        $user = CentralMsUser::query()
            ->with('tenants')
            ->findOrFail($userId);

        $attributes = $user->getAttributes();
        $currentTenantIds = $user->tenants->pluck('id')->all();

        foreach ($user->tenants as $tenant) {
            $this->setTenantConnection($tenant);

            $tenantUser = TenantMsUser::query()->firstOrNew(['id' => $user->getKey()]);
            $tenantUser->forceFill($attributes);
            $tenantUser->save();
        }

        $removedTenantIds = array_values(array_diff($previousTenantIds, $currentTenantIds));

        if ($removedTenantIds === []) {
            return;
        }

        $removedTenants = MsTenantCentral::query()
            ->whereIn('id', $removedTenantIds)
            ->get();

        foreach ($removedTenants as $tenant) {
            $this->setTenantConnection($tenant);
            TenantMsUser::query()->whereKey($user->getKey())->delete();
        }
    }

    public function deleteByUserId(int|string $userId, array $tenantIds): void
    {
        if ($tenantIds === []) {
            return;
        }

        $tenants = MsTenantCentral::query()
            ->whereIn('id', $tenantIds)
            ->get();

        foreach ($tenants as $tenant) {
            $this->setTenantConnection($tenant);
            TenantMsUser::query()->whereKey($userId)->delete();
        }
    }

    private function setTenantConnection(MsTenantCentral $tenant): void
    {
        Config::set('database.connections.tenant', [
            'driver' => 'pgsql',
            'host' => $tenant->db_host,
            'port' => (int) $tenant->db_port,
            'database' => $tenant->db_name,
            'username' => $tenant->db_user,
            'password' => $tenant->db_password,
            'charset' => 'utf8',
            'prefix' => '',
            'search_path' => 'public',
            'sslmode' => Config::get('database.connections.tenant.sslmode', 'prefer'),
        ]);

        DB::disconnect('tenant');
        DB::purge('tenant');
        DB::reconnect('tenant');
    }
}
