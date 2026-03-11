<?php

namespace App\Models\Master\Inventory;

use App\Models\Master\Buku\BukuStock;
use App\Models\Inventory\MsPartVariant;
use App\Models\Inventory\PartStandard;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsPart extends Model
{
    use HasFactory;

    protected $connection = 'pgsql';
    protected $table = 'ms_part';

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'integer';

    protected $guarded = ['created_at', 'updated_at'];

    public function inventoryType()
    {
        return $this->belongsTo(MsInventoryType::class, 'inventory_type_id');
    }

    public function category()
    {
        return $this->belongsTo(MsPartCategory::class, 'category_id');
    }

    public function brand()
    {
        return $this->belongsTo(MsPartBrand::class, 'brand_id');
    }

    public function specification()
    {
        return $this->belongsTo(MsPartSpecification::class, 'specification_id');
    }

    public function variant()
    {
        return $this->belongsTo(MsPartVariant::class, 'variant_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(MsWarehouse::class, 'defered_warehouse_id', 'id');
    }

    public function type()
    {
        return $this->belongsTo(MsPartType::class, 'type_id');
    }

    public function units()
    {
        return $this->hasMany(MsPartUnit::class, 'part_id');
    }

    public function mainUnit()
    {
        return $this->hasOne(MsPartUnit::class, 'part_id')->where('sequence', 1);
    }

    public function standards()
    {
        return $this->hasMany(PartStandard::class, 'part_id');
    }

    public function stocks()
    {
        return $this->hasMany(BukuStock::class, 'part_id', 'id');
    }
}
