<?php

namespace Keysoft\HelperLibrary\Models\Tenant\Master\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Keysoft\HelperLibrary\Models\BaseModelTenant;
use Keysoft\HelperLibrary\Traits\AuditedBy;

class MsAccountType extends BaseModelTenant
{
    use HasFactory, AuditedBy;

    protected $connection = 'tenant';
    protected $table = 'ms_account_type';

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'integer';

    protected $guarded = ['created_at', 'updated_at'];

    protected $casts = [
        'json' => 'array',
    ];

    public function coas()
    {
        return $this->hasMany(MsCOA::class, 'account_type_id');
    }

    public function coasMany()
    {
        return $this->belongsToMany(MsCOA::class)->using(MsAccountMappingType::class)->withPivot(['account_no', 'account_type', 'status', 'created_by', 'updated_by', 'json'])->withTimestamps();
    }

    // public function accountMappingTypes()
    // {
    //     return $this->hasMany(MsAccountMappingType::class, 'account_type_id');
    // }
}
