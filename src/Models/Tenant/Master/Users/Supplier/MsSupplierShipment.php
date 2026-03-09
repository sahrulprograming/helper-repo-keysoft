<?php

namespace Keysoft\HelperLibrary\Models\Tenant\Master\Users\Supplier;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Keysoft\HelperLibrary\Models\BaseModelTenant;
use Keysoft\HelperLibrary\Models\Tenant\Master\Common\MsProvince;
use Keysoft\HelperLibrary\Traits\AuditedBy;

class MsSupplierShipment extends BaseModelTenant
{
    use HasFactory, AuditedBy;

    protected $connection = 'tenant';
    protected $table = 'ms_supplier_shipment';

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'integer';

    protected $guarded = ['created_at', 'updated_at'];

    public function province()
    {
        return $this->belongsTo(MsProvince::class, 'province_id', 'id');
    }

    public function supplier()
    {
        return $this->belongsTo(MsSupplier::class, 'supplier_id', 'id');
    }
}
