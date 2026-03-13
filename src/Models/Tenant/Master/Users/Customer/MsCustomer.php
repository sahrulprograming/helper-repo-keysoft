<?php

namespace Keysoft\HelperLibrary\Models\Tenant\Master\Users\Customer;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Keysoft\HelperLibrary\Models\BaseModelTenant;
use Keysoft\HelperLibrary\Models\Tenant\Master\Common\MsCity;
use Keysoft\HelperLibrary\Models\Tenant\Master\Common\MsCountry;
use Keysoft\HelperLibrary\Models\Tenant\Master\Common\MsCurrency;
use Keysoft\HelperLibrary\Models\Tenant\Master\Common\MsDistrict;
use Keysoft\HelperLibrary\Models\Tenant\Master\Common\MsProvince;
use Keysoft\HelperLibrary\Models\Tenant\Master\Common\MsSubDistrict;
use Keysoft\HelperLibrary\Models\Tenant\Master\Users\MsDivision;
use Keysoft\HelperLibrary\Models\Tenant\Master\Users\MsEmployee;
use Keysoft\HelperLibrary\Traits\AuditedBy;

class MsCustomer extends BaseModelTenant
{
    use HasFactory, AuditedBy;

    protected $connection = 'tenant';
    protected $table = 'ms_customer';

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'integer';

    protected $guarded = ['created_at', 'updated_at'];

    protected $casts = [
        'json' => 'array',
        'credit_limit' => 'double',
        'invoice_limit' => 'double',
        'deal_transaction_value' => 'double',
    ];

    public function customerCategory()
    {
        return $this->belongsTo(MsCustomerCategory::class, 'category_id', 'id');
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

    public function salesman()
    {
        return $this->belongsTo(MsEmployee::class, 'salesman_id', 'id');
    }

    public function currency()
    {
        return $this->belongsTo(MsCurrency::class, 'currency_id', 'id');
    }

    public function subdistrict()
    {
        return $this->belongsTo(MsSubDistrict::class, 'sub_district_id', 'id');
    }

    public function shipments()
    {
        return $this->hasMany(MsCustomerShipment::class, 'customer_id', 'id');
    }

    public function billings()
    {
        return $this->hasMany(MsCustomerBilling::class, 'customer_id', 'id');
    }

    public function contacts()
    {
        return $this->hasMany(MsCustomerContact::class, 'customer_id', 'id');
    }
}
