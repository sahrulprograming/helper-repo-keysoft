<?php

namespace App\Models\Master\Accounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MsCategoryCOA extends Model
{
    use HasFactory;
    protected $connection = 'pgsql';
    protected $table = 'ms_category_coa';

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $guarded = ['id', 'created_at', 'updated_at'];
}
