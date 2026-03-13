<?php

namespace Keysoft\HelperLibrary\Models\Tenant\Master\Users;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Keysoft\HelperLibrary\Models\BaseModelTenant;
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

    protected $casts = [
        'json' => 'array',
    ];

    public function subDivision()
    {
        return $this->belongsTo(MsDivision::class, 'sub_division_id', 'id');
    }

    public function childDivisions()
    {
        return $this->hasMany(MsDivision::class, 'sub_division_id', 'id');
    }

    public function staffInCharge()
    {
        return $this->belongsTo(MsEmployee::class, 'staff_in_charge_id', 'id');
    }

    public function contactPerson()
    {
        return $this->belongsTo(MsEmployee::class, 'contact_person_id', 'id');
    }

    public function dayOffApproval()
    {
        return $this->belongsTo(MsEmployee::class, 'day_off_approval_id', 'id');
    }

    public function warehouses()
    {
        return $this->hasMany(MsWarehouse::class, 'division_id', 'id');
    }

    public function employees()
    {
        return $this->hasMany(MsEmployee::class, 'division_id', 'id');
    }
}
