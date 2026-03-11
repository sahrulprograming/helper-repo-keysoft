<?php

namespace App\Models\Master\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsPartSpecification extends Model
{
    use HasFactory;

    protected $connection = 'pgsql';
    protected $table = 'ms_part_specification';

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'integer';

    protected $guarded = ['created_at', 'updated_at'];
}
