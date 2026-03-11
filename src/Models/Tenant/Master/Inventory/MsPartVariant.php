<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;

class MsPartVariant extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'ms_part_variant';

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'integer';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = ['created_at', 'updated_at'];
}
