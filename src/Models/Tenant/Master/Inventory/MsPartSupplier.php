<?php

namespace Keysoft\HelperLibrary\Models\Tenant\Master\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Keysoft\HelperLibrary\Models\BaseModelTenant;
use Keysoft\HelperLibrary\Models\Tenant\Master\Users\Supplier\MsSupplier;
use Keysoft\HelperLibrary\Traits\AuditedBy;

class MsPartSupplier extends BaseModelTenant
{
    use HasFactory, AuditedBy;

    protected $connection = 'tenant';
    protected $table = 'ms_part_supplier';

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'integer';

    protected $guarded = ['created_at', 'updated_at'];

    protected $casts = [
        'json' => 'array',
        'percentage_order' => 'double',
    ];

    public function part()
    {
        return $this->belongsTo(MsPart::class, 'part_id', 'id');
    }

    public function supplier()
    {
        return $this->belongsTo(MsSupplier::class, 'supplier_id', 'id');
    }

    public function deliveryUnit()
    {
        return $this->belongsTo(MsUnit::class, 'delivery_unit_id', 'id');
    }
}
