<?php

namespace Keysoft\HelperLibrary\Models\Tenant\Master\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Keysoft\HelperLibrary\Models\BaseModelTenant;
use Keysoft\HelperLibrary\Models\Tenant\Master\Users\MsDivision;
use Keysoft\HelperLibrary\Models\Tenant\Master\Users\MsEmployee;
use Keysoft\HelperLibrary\Traits\AuditedBy;

class MsWarehouse extends BaseModelTenant
{
    use HasFactory, AuditedBy;

    protected $connection = 'tenant';
    protected $table = 'ms_warehouse';

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'integer';

    protected $guarded = ['created_at', 'updated_at'];

    protected $casts = [
        'json' => 'array',
    ];

    public function parent()
    {
        return $this->belongsTo(MsWarehouse::class, 'parent_id', 'id');
    }

    public function children()
    {
        return $this->hasMany(MsWarehouse::class, 'parent_id', 'id');
    }

    public function deferredParts()
    {
        return $this->hasMany(MsPart::class, 'deferred_warehouse_id', 'id');
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class, 'warehouse_id', 'id');
    }

    public function staffInCharge()
    {
        return $this->belongsTo(MsEmployee::class, 'staff_in_charge_id', 'id');
    }

    public function division()
    {
        return $this->belongsTo(MsDivision::class, 'division_id', 'id');
    }
}
