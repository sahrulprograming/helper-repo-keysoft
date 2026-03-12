<?php

namespace Keysoft\HelperLibrary\Models\Tenant\Master\Accounting;

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
    protected $keyType = 'integer';
    protected $guarded = ['created_at', 'updated_at'];

    public function coas()
    {
        return $this->hasMany(MsCOA::class, 'category_id');
    }
}
