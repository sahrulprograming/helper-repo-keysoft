<?php

namespace Keysoft\HelperLibrary\Models\Tenant\Master\Users\Customer;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Keysoft\HelperLibrary\Models\BaseModelTenant;
use Keysoft\HelperLibrary\Models\Tenant\Master\Common\MsProvince;
use Keysoft\HelperLibrary\Traits\AuditedBy;

class MsCustomerBilling extends BaseModelTenant
{
    use HasFactory, AuditedBy;

    protected $connection = 'tenant';
    protected $table = 'ms_customer_billing';

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'integer';

    protected $guarded = ['created_at', 'updated_at'];

    public function customer()
    {
        return $this->belongsTo(MsCustomer::class, 'customer_id', 'id');
    }

    public function province()
    {
        return $this->belongsTo(MsProvince::class, 'province_id', 'id');
    }
}
