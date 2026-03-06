<?php

namespace Keysoft\HelperLibrary\Models\Master\Users\Supplier;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Keysoft\HelperLibrary\Models\BaseModelTenant;
use Keysoft\HelperLibrary\Traits\AuditedBy;

class MsSupplierContact extends BaseModelTenant
{
    use HasFactory, AuditedBy;

    protected $connection = 'tenant';
    protected $table = 'ms_supplier_contact';

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'integer';

    protected $guarded = ['created_at', 'updated_at'];

    public function supplier()
    {
        return $this->belongsTo(MsSupplier::class, 'supplier_id', 'id');
    }
}
