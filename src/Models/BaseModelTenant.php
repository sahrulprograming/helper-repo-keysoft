<?php

namespace Keysoft\HelperLibrary\Models;

use Illuminate\Database\Eloquent\Model;
use Keysoft\HelperLibrary\Support\TenantConnection;

class BaseModelTenant extends Model
{
    protected static function booted()
    {
        TenantConnection::set();
    }
}
