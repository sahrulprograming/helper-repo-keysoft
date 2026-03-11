<?php

namespace App\Models\Master\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $connection= 'pgsql';
    protected $table = 'inventory';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = ['created_at', 'updated_at'];

    protected $casts = [
        'qty' => 'double'
    ];

    public function part()
    {
        return $this->belongsTo(MsPart::class, 'part_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(MsWarehouse::class, 'warehouse_id');
    }
}
