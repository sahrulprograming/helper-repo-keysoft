<?php

namespace Keysoft\HelperLibrary\Models\Master\Common;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Keysoft\HelperLibrary\Traits\AuditedBy;

class MsVehicle extends Model
{
    use HasFactory, AuditedBy;

    protected $connection= 'tenant';
    protected $table = 'ms_vehicle';

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'integer';

    protected $guarded = ['created_at', 'updated_at'];
}
