<?php

namespace Keysoft\HelperLibrary\Models\Tenant\Master\Users\Supplier;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Keysoft\HelperLibrary\Models\BaseModelTenant;
use Keysoft\HelperLibrary\Models\Tenant\Master\Common\MsCurrency;
use Keysoft\HelperLibrary\Traits\AuditedBy;

class MsSupplierPayment extends BaseModelTenant
{
    use HasFactory, AuditedBy;

    protected $connection = 'tenant';
    protected $table = 'ms_supplier_payment';

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'integer';

    protected $guarded = ['created_at', 'updated_at'];

    public function supplier()
    {
        return $this->belongsTo(MsSupplier::class, 'supplier_id', 'id');
    }

    public function currency()
    {
        return $this->belongsTo(MsCurrency::class, 'currency_id', 'id');
    }
}
