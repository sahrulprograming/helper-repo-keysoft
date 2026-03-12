<?php

namespace Keysoft\HelperLibrary\Models\Tenant\Master\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Keysoft\HelperLibrary\Models\BaseModelTenant;
use Keysoft\HelperLibrary\Traits\AuditedBy;

class MsUnit extends BaseModelTenant
{
    use HasFactory, AuditedBy;

    protected $connection = 'tenant';
    protected $table = 'ms_unit';

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'integer';

    protected $guarded = ['created_at', 'updated_at'];

    public function partUnits()
    {
        return $this->hasMany(MsPartUnit::class, 'unit_id', 'id');
    }

    public function supplierDeliveryUnits()
    {
        return $this->hasMany(MsPartSupplier::class, 'delivery_unit_id', 'id');
    }

    public function volumeParts()
    {
        return $this->hasMany(MsPart::class, 'volume_unit_id', 'id');
    }

    public function weightParts()
    {
        return $this->hasMany(MsPart::class, 'weight_unit_id', 'id');
    }
}
