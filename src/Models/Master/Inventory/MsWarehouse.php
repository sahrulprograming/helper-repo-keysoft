<?php

namespace App\Models\Master\Inventory;

use App\Models\MsDivision;
use App\Models\MsEmployee;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsWarehouse extends Model
{
    use HasFactory;

    protected $connection = 'pgsql';
    protected $table = 'ms_warehouse';

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'integer';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = ['created_at', 'updated_at'];

    public function parent()
    {
        return $this->belongsTo(MsWarehouse::class, 'parent_id', 'id');
    }

    // public function staffInCharge()
    // {
    //     return $this->belongsTo(MsEmployee::class, 'staff_in_charge_id', 'id');
    // }

    // public function division()
    // {
    //     return $this->belongsTo(MsDivision::class, 'division_id', 'id');
    // }
}
