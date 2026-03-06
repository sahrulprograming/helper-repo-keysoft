<?php

namespace App\Models\Master\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Keysoft\HelperLibrary\Models\BaseModelTenant;
use Keysoft\HelperLibrary\Traits\AuditedBy;

class MsCategoryCOA extends BaseModelTenant
{
    use HasFactory, AuditedBy;
    
    protected $connection = 'tenant';
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
