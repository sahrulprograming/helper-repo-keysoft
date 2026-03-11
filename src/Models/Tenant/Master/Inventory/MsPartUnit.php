<?php

namespace App\Models\Master\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsPartUnit extends Model
{
    use HasFactory;

    protected $connection = 'pgsql';
    protected $table = 'ms_part_unit';

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'integer';

    protected $guarded = ['created_at', 'updated_at'];

    protected $casts = [
        'conversion' => 'double',
    ];

    public function part()
    {
        return $this->belongsTo(MsPart::class, 'part_id');
    }

    public function unit1()
    {
        return $this->belongsTo(MsUnit::class, 'unit_id1');
    }

    public function unit2()
    {
        return $this->belongsTo(MsUnit::class, 'unit_id2');
    }
}
