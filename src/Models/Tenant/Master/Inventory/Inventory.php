<?php

namespace Keysoft\HelperLibrary\Models\Tenant\Master\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Keysoft\HelperLibrary\Models\BaseModelTenant;
use Keysoft\HelperLibrary\Traits\AuditedBy;

class Inventory extends BaseModelTenant
{
    use HasFactory, AuditedBy;

    protected $connection = 'tenant';
    protected $table = 'inventory';

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'integer';

    protected $guarded = ['created_at', 'updated_at'];

    protected $casts = [
        'qty' => 'double',
    ];

    public function part()
    {
        return $this->belongsTo(MsPart::class, 'part_id', 'id');
    }

    public function warehouse()
    {
        return $this->belongsTo(MsWarehouse::class, 'warehouse_id', 'id');
    }
}
