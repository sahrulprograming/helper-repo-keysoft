<?php

namespace Keysoft\HelperLibrary\Models\Tenant\Master\Users\Supplier;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Keysoft\HelperLibrary\Models\BaseModelTenant;
use Keysoft\HelperLibrary\Traits\AuditedBy;

class MsSupplierCategory extends BaseModelTenant
{
    use HasFactory, AuditedBy;

    protected $connection = 'tenant';
    protected $table = 'ms_supplier_category';

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'integer';

    protected $guarded = ['created_at', 'updated_at'];

    public function suppliers()
    {
        return $this->hasMany(MsSupplier::class, 'category_id', 'id');
    }
}
