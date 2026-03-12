<?php

namespace Keysoft\HelperLibrary\Models\Tenant\Master\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Keysoft\HelperLibrary\Models\BaseModelTenant;
use Keysoft\HelperLibrary\Traits\AuditedBy;

class MsInventoryType extends BaseModelTenant
{
    use HasFactory, AuditedBy;

    protected $connection = 'tenant';
    protected $table = 'ms_inventory_type';

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'integer';

    protected $guarded = ['created_at', 'updated_at'];

    public function parts()
    {
        return $this->hasMany(MsPart::class, 'inventory_type_id', 'id');
    }
}
