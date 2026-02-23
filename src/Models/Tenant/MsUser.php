<?php


namespace Keysoft\HelperLibrary\Models\Tenant;

use Keysoft\HelperLibrary\Models\BaseModelTenant;

class MsUser extends BaseModelTenant
{
    public const TABLE_NAME = 'ms_users';
    protected $table = self::TABLE_NAME;
    protected $connection = 'tenant';

    protected $guarded = [
        'id',
    ];

    protected $hidden = ['password'];
}
