<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsPartStandard extends Model
{
    use HasFactory;

    protected $connection = 'sqlsrv';
    protected $table = 'Ms_PartStandard';

    protected $primaryKey = 'StandardID';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = ['created_at', 'updated_at'];
}
