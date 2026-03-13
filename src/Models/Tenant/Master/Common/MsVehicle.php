<?php

namespace Keysoft\HelperLibrary\Models\Tenant\Master\Common;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Keysoft\HelperLibrary\Models\BaseModelTenant;
use Keysoft\HelperLibrary\Traits\AuditedBy;

class MsVehicle extends BaseModelTenant
{
    use HasFactory, AuditedBy;

    protected $connection= 'tenant';
    protected $table = 'ms_vehicle';

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'integer';

    protected $guarded = ['created_at', 'updated_at'];

    protected $casts = [
        'json' => 'array',
    ];
}
