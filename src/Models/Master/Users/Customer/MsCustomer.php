<?php

namespace Keysoft\HelperLibrary\Models\Master\Users\Customer;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Keysoft\HelperLibrary\Models\BaseModelTenant;
use Keysoft\HelperLibrary\Models\Master\Common\MsCountry;
use Keysoft\HelperLibrary\Models\Master\Common\MsProvince;
use Keysoft\HelperLibrary\Models\Master\Common\MsSubDistrict;
use Keysoft\HelperLibrary\Models\Master\Users\MsDivision;
use Keysoft\HelperLibrary\Models\Master\Users\MsEmployee;
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

    public function salesman()
    {
        return $this->belongsTo(MsEmployee::class, 'salesman_id', 'id');
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
