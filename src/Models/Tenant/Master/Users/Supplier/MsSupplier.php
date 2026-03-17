<?php

namespace Keysoft\HelperLibrary\Models\Tenant\Master\Users\Supplier;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Keysoft\HelperLibrary\Models\BaseModelTenant;
use Keysoft\HelperLibrary\Models\Tenant\Master\Common\MsCity;
use Keysoft\HelperLibrary\Models\Tenant\Master\Common\MsCountry;
use Keysoft\HelperLibrary\Models\Tenant\Master\Common\MsDistrict;
use Keysoft\HelperLibrary\Models\Tenant\Master\Common\MsProvince;
use Keysoft\HelperLibrary\Models\Tenant\Master\Common\MsSubDistrict;
use Keysoft\HelperLibrary\Models\Tenant\Master\Inventory\MsPart;
use Keysoft\HelperLibrary\Models\Tenant\Master\Users\MsDivision;
use Keysoft\HelperLibrary\Traits\AuditedBy;

class MsSupplier extends BaseModelTenant
{
    use HasFactory, AuditedBy;

    protected $connection = 'tenant';
    protected $table = 'ms_supplier';

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'integer';

    protected $guarded = ['created_at', 'updated_at'];

    protected $casts = [
        'json' => 'array',
        'account_payable_limit' => 'double',
    ];

    public function parts(): BelongsToMany
    {
        return $this->belongsToMany(MsPart::class, 'ms_part_supplier', 'supplier_id', 'part_id')
            ->withPivot(['delivery_time', 'delivery_unit_id', 'status', 'created_by', 'updated_by', 'json'])
            ->withTimestamps();
    }

    public function supplierCategory()
    {
        return $this->belongsTo(MsSupplierCategory::class, 'category_id', 'id');
    }

    public function division()
    {
        return $this->belongsTo(MsDivision::class, 'division_id', 'id');
    }

    public function country()
    {
        return $this->belongsTo(MsCountry::class, 'country_id', 'id');
    }

    public function province()
    {
        return $this->belongsTo(MsProvince::class, 'province_id', 'id');
    }

    public function city()
    {
        return $this->belongsTo(MsCity::class, 'city_id', 'id');
    }

    public function district()
    {
        return $this->belongsTo(MsDistrict::class, 'district_id', 'id');
    }

    public function subdistrict()
    {
        return $this->belongsTo(MsSubDistrict::class, 'sub_district_id', 'id');
    }

    public function shipments()
    {
        return $this->hasMany(MsSupplierShipment::class, 'supplier_id', 'id');
    }

    public function billings()
    {
        return $this->hasMany(MsSupplierBilling::class, 'supplier_id', 'id');
    }

    public function contacts()
    {
        return $this->hasMany(MsSupplierContact::class, 'supplier_id', 'id');
    }

    public function payments()
    {
        return $this->hasMany(MsSupplierPayment::class, 'supplier_id', 'id');
    }
}
