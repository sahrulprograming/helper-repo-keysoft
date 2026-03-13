<?php

namespace Keysoft\HelperLibrary\Models\Tenant\Master\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Keysoft\HelperLibrary\Models\BaseModelTenant;
use Keysoft\HelperLibrary\Traits\AuditedBy;

class MsPart extends BaseModelTenant
{
    use HasFactory, AuditedBy;

    protected $connection = 'tenant';
    protected $table = 'ms_part';

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'integer';

    protected $guarded = ['created_at', 'updated_at'];

    protected $casts = [
        'json' => 'array',
    ];

    public function inventoryType()
    {
        return $this->belongsTo(MsInventoryType::class, 'inventory_type_id', 'id');
    }

    public function category()
    {
        return $this->belongsTo(MsPartCategory::class, 'category_id', 'id');
    }

    // public function brand()
    // {
    //     return $this->belongsTo(MsPartBrand::class, 'brand_id', 'id');
    // }

    public function specification()
    {
        return $this->belongsTo(MsPartSpecification::class, 'specification_id', 'id');
    }

    public function variant()
    {
        return $this->belongsTo(MsPartVariant::class, 'variant_id', 'id');
    }

    public function warehouse()
    {
        return $this->belongsTo(MsWarehouse::class, 'deferred_warehouse_id', 'id');
    }

    public function type()
    {
        return $this->belongsTo(MsPartType::class, 'type_id', 'id');
    }

    public function units()
    {
        return $this->hasMany(MsPartUnit::class, 'part_id', 'id');
    }

    public function volumeUnit()
    {
        return $this->belongsTo(MsUnit::class, 'volume_unit_id', 'id');
    }

    public function weightUnit()
    {
        return $this->belongsTo(MsUnit::class, 'weight_unit_id', 'id');
    }

    public function suppliers()
    {
        return $this->hasMany(MsPartSupplier::class, 'part_id', 'id');
    }

    public function bomHeaders()
    {
        return $this->hasMany(MsBomHD::class, 'part_id', 'id');
    }

    public function bomDetails()
    {
        return $this->hasMany(MsBomDT::class, 'part_id', 'id');
    }

    // public function inventories()
    // {
    //     return $this->hasMany(MsInventory::class, 'part_id', 'id');
    // }
}
