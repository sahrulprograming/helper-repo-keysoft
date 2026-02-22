<?php

namespace Keysoft\HelperLibrary\Models;

use App\Support\TenantConnection;
use Illuminate\Database\Eloquent\Model;

class BaseModelTenant extends Model
{
    protected static function booted()
    {
        TenantConnection::set();
    }
}
