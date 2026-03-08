<?php

namespace Keysoft\HelperLibrary\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class TenantMigration extends Model
{
    protected $connection = 'tenant';

    protected $table = 'migrations';

    public $timestamps = false;

    protected $guarded = [];
}

