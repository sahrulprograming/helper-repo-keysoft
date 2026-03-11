<?php

namespace App\Models\Master\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Master\Users\Supplier\MsSupplier;

class MsPartSupplier extends Model
{
    use HasFactory;
    
    protected $connection = 'pgsql';
    protected $table = 'ms_part_supplier';

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'integer';

    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $casts = [
        'percentage_order' => 'double',
    ];

    public function part()
    {
        return $this->belongsTo(MsPart::class, 'part_id');
    }

    public function supplier()
    {
        return $this->belongsTo(MsSupplier::class, 'supplier_id');
    }
}
