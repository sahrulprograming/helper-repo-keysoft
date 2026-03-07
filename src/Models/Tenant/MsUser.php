<?php


namespace Keysoft\HelperLibrary\Models\Tenant;

use Keysoft\HelperLibrary\Models\BaseModelTenant;
use Keysoft\HelperLibrary\Support\TenantUserOuterSynchronizer;

class MsUser extends BaseModelTenant
{
    public const TABLE_NAME = 'ms_users';
    protected $table = self::TABLE_NAME;
    protected $connection = 'tenant';

    protected $guarded = [
        'id',
    ];

    protected $hidden = ['password'];

    protected static function booted(): void
    {
        parent::booted();

        static::creating(function (self $tenantUser): void {
            static::synchronizer()->alignTenantUserPrimaryKey($tenantUser);
        });

        static::saved(function (self $tenantUser): void {
            $synchronizer = static::synchronizer();
            $synchronizer->sync($tenantUser);
            $synchronizer->syncTenantPrimaryKeySequence();
        });

        static::deleted(function (self $tenantUser): void {
            static::synchronizer()->detach($tenantUser);
        });
    }

    protected static function synchronizer(): TenantUserOuterSynchronizer
    {
        return new TenantUserOuterSynchronizer();
    }
}
