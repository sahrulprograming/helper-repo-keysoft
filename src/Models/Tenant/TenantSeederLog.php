<?php

namespace Keysoft\HelperLibrary\Models;

use Illuminate\Database\Eloquent\Model;

class TenantSeederLog extends Model
{
    protected $connection = 'tenant';

    protected $table = 'tenant_seeders';

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'executed_at' => 'datetime',
        ];
    }
}

