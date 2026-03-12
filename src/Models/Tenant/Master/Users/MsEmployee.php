<?php

namespace Keysoft\HelperLibrary\Models\Tenant\Master\Users;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Keysoft\HelperLibrary\Models\BaseModelTenant;
use Keysoft\HelperLibrary\Models\Tenant\Master\Accounting\MsBank;
use Keysoft\HelperLibrary\Models\Tenant\Master\Common\MsCity;
use Keysoft\HelperLibrary\Models\Tenant\Master\Common\MsCountry;
use Keysoft\HelperLibrary\Traits\AuditedBy;

class MsEmployee extends BaseModelTenant
{
    use HasFactory, AuditedBy;

    protected $connection = 'tenant';
    protected $table = 'ms_employee';

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'integer';

    protected $guarded = ['created_at', 'updated_at'];

    public function division()
    {
        return $this->belongsTo(MsDivision::class, 'division_id', 'id');
    }

    public function country()
    {
        return $this->belongsTo(MsCountry::class, 'country_id', 'id');
    }

    public function city()
    {
        return $this->belongsTo(MsCity::class, 'city_id', 'id');
    }

    public function bank()
    {
        return $this->belongsTo(MsBank::class, 'bank_id', 'id');
    }

    public function reference()
    {
        return $this->belongsTo(MsEmployee::class, 'reference_id', 'id');
    }

    public function supervisor()
    {
        return $this->belongsTo(MsEmployee::class, 'supervisor_id', 'id');
    }

    public function references()
    {
        return $this->hasMany(MsEmployee::class, 'reference_id', 'id');
    }

    public function supervisees()
    {
        return $this->hasMany(MsEmployee::class, 'supervisor_id', 'id');
    }

    public function divisionsAsStaffInCharge()
    {
        return $this->hasMany(MsDivision::class, 'staff_in_charge_id', 'id');
    }

    public function divisionsAsContactPerson()
    {
        return $this->hasMany(MsDivision::class, 'contact_person_id', 'id');
    }

    public function divisionsAsDayOffApproval()
    {
        return $this->hasMany(MsDivision::class, 'day_off_approval_id', 'id');
    }

    public function warehousesAsStaffInCharge()
    {
        return $this->hasMany(\Keysoft\HelperLibrary\Models\Tenant\Master\Inventory\MsWarehouse::class, 'staff_in_charge_id', 'id');
    }

    public function routeNotificationForMail($notification)
    {
        return $this->email ?? null;
    }
}
