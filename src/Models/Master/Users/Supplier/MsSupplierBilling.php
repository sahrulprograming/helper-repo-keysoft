<?php

namespace Keysoft\HelperLibrary\Models\Master\Users\Supplier;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Keysoft\HelperLibrary\Models\BaseModelTenant;
use Keysoft\HelperLibrary\Models\Master\Common\MsProvince;
use Keysoft\HelperLibrary\Traits\AuditedBy;

class MsSupplierBilling extends BaseModelTenant
{
    use HasFactory, AuditedBy;

    protected $connection = 'tenant';
    protected $table = 'ms_supplier_billing';

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
