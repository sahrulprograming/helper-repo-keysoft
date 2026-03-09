<?php

namespace Keysoft\HelperLibrary\Models\Tenant\Master\Users;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Keysoft\HelperLibrary\Models\BaseModelTenant;
use Keysoft\HelperLibrary\Models\Tenant\Master\Admin\MsDivisionModuleMapping;
use Keysoft\HelperLibrary\Models\Tenant\Master\Admin\MsUserPermissionMappingHD;
use Keysoft\HelperLibrary\Models\Tenant\Master\Inventory\MsWarehouse;
use Keysoft\HelperLibrary\Traits\AuditedBy;

class MsDivision extends BaseModelTenant
{
    use HasFactory, AuditedBy;

    protected $connection = 'tenant';
    protected $table = 'ms_division';

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'integer';

    protected $guarded = ['created_at', 'updated_at'];

    public function subDivision()
    {
        return $this->belongsTo(MsDivision::class, 'sub_division_id', 'id');
    }

    public function warehouse()
    {
        return $this->hasOne(MsWarehouse::class, 'division_id', 'id');
    }

    // public function divisionModuleMappings()
    // {
    //     return $this->hasMany(MsDivisionModuleMapping::class, 'division_id', 'id')
    //         ->with('action');
    // }

    // public function userPermissionMappings()
    // {
    //     return $this->hasMany(MsUserPermissionMappingHD::class, 'division_id', 'id');
    // }
}
