<?php

namespace Keysoft\HelperLibrary\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Keysoft\HelperLibrary\Models\MsUser as CentralMsUser;
use Keysoft\HelperLibrary\Models\Tenant\MsUser as TenantMsUser;

class TenantUserOuterSynchronizer
{
    public function canSyncFromCurrentContext(): bool
    {
        if (! App::bound('session')) {
            return false;
        }

        return $this->resolveActiveTenantId() > 0;
    }

    public function alignTenantUserPrimaryKey(TenantMsUser $tenantUser): void
    {
        if (! $this->canSyncFromCurrentContext()) {
            return;
        }

        if ($tenantUser->getKey()) {
            return;
        }

        $username = trim((string) $tenantUser->username);

        if ($username === '') {
            return;
        }

        $centralUser = CentralMsUser::query()
            ->where('username', $username)
            ->first();

        if (! $centralUser) {
            return;
        }

        $tenantKey = $tenantUser->getKeyName();

        if (TenantMsUser::query()->whereKey($centralUser->getKey())->exists()) {
            return;
        }

        $tenantUser->setAttribute($tenantKey, $centralUser->getKey());
    }

    public function sync(TenantMsUser $tenantUser): void
    {
        if (! $this->canSyncFromCurrentContext()) {
            return;
        }

        $tenantId = $this->resolveActiveTenantId();
        $attributes = $this->extractCentralAttributes($tenantUser);

        $centralUser = CentralMsUser::query()->find($tenantUser->getKey());

        if (! $centralUser) {
            $centralUser = CentralMsUser::query()
                ->where('username', (string) $tenantUser->username)
                ->first();
        }

        if (! $centralUser) {
            $centralUser = new CentralMsUser();
            $centralUser->setAttribute($centralUser->getKeyName(), $tenantUser->getKey());
        }

        $centralUser->forceFill($attributes);
        $centralUser->save();

        $this->upsertTenantMapping((int) $centralUser->getKey(), $tenantId);
    }

    public function detach(TenantMsUser $tenantUser): void
    {
        if (! $this->canSyncFromCurrentContext()) {
            return;
        }

        $tenantId = $this->resolveActiveTenantId();

        $centralUser = CentralMsUser::query()->find($tenantUser->getKey());

        if (! $centralUser) {
            $centralUser = CentralMsUser::query()
                ->where('username', (string) $tenantUser->username)
                ->first();
        }

        if (! $centralUser) {
            return;
        }

        $centralUserId = (int) $centralUser->getKey();

        DB::table(CentralMsUser::PIVOT_USER_TENANT_TABLE_NAME)
            ->where('user_id', $centralUserId)
            ->where('tenant_id', $tenantId)
            ->delete();

        $hasAnyTenantMapping = DB::table(CentralMsUser::PIVOT_USER_TENANT_TABLE_NAME)
            ->where('user_id', $centralUserId)
            ->exists();

        if (! $hasAnyTenantMapping) {
            $centralUser->delete();
        }
    }

    public function syncTenantPrimaryKeySequence(): void
    {
        if (DB::connection('tenant')->getDriverName() !== 'pgsql') {
            return;
        }

        DB::connection('tenant')->statement(
            "SELECT setval(pg_get_serial_sequence('ms_users', 'id'), COALESCE((SELECT MAX(id) FROM ms_users), 1), true)"
        );
    }

    private function resolveActiveTenantId(): int
    {
        if (! App::bound('session')) {
            return 0;
        }

        return max(0, (int) data_get(Session::get('active_tenant'), 'id', 0));
    }

    /**
     * @return array<string, mixed>
     */
    private function extractCentralAttributes(TenantMsUser $tenantUser): array
    {
        return [
            'username' => $tenantUser->username,
            'password' => $tenantUser->password,
            'status' => $tenantUser->status,
            'expired_at' => $tenantUser->expired_at,
        ];
    }

    private function upsertTenantMapping(int $userId, int $tenantId): void
    {
        $pivotTable = CentralMsUser::PIVOT_USER_TENANT_TABLE_NAME;
        $now = Carbon::now();
        $authId = Auth::id();

        $existing = DB::table($pivotTable)
            ->where('user_id', $userId)
            ->where('tenant_id', $tenantId);

        if ($existing->exists()) {
            $existing->update([
                'updated_by' => $authId,
                'updated_at' => $now,
            ]);

            return;
        }

        DB::table($pivotTable)->insert([
            'user_id' => $userId,
            'tenant_id' => $tenantId,
            'created_by' => $authId,
            'updated_by' => $authId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
