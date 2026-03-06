<?php

namespace Keysoft\HelperLibrary\Models\Master\Users\Supplier;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Keysoft\HelperLibrary\Models\BaseModelTenant;
use Keysoft\HelperLibrary\Models\Master\Common\MsCountry;
use Keysoft\HelperLibrary\Models\Master\Common\MsProvince;
use Keysoft\HelperLibrary\Models\Master\Common\MsSubDistrict;
use Keysoft\HelperLibrary\Models\Master\Users\MsDivision;
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
        'account_payable_limit' => 'double',
    ];

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
