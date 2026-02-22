<?php


namespace Keysoft\HelperLibrary\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class MsUser extends Model
{
    public const TABLE_NAME = 'ms_users';
    protected $table = self::TABLE_NAME;
    protected $connection = 'tenant';

    protected $guarded = [
        'id',
    ];

    protected $hidden = ['password'];
}
