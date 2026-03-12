<?php

namespace Keysoft\HelperLibrary\Models\Tenant\Master\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Keysoft\HelperLibrary\Models\BaseModelTenant;
use Keysoft\HelperLibrary\Models\Tenant\Master\Common\MsCurrency;
use Keysoft\HelperLibrary\Traits\AuditedBy;

class MsCOA extends BaseModelTenant
{
    use HasFactory, AuditedBy;
    
    protected $connection = 'tenant';
    protected $table = 'ms_coa';

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'integer';
    protected $guarded = ['created_at', 'updated_at'];

    public function accountType()
    {
        return $this->belongsTo(MsAccountType::class, 'account_type_id');
    }

    public function bank()
    {
        return $this->belongsTo(MsBank::class, 'bank_id');
    }

    public function currency()
    {
        return $this->belongsTo(MsCurrency::class, 'currency_id');
    }

    public function parent()
    {
        return $this->belongsTo(MsCOA::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(MsCOA::class, 'parent_id');
    }

    public function category()
    {
        return $this->belongsTo(MsCategoryCOA::class, 'category_id');
    }

    public function accountMappingTypes()
    {
        return $this->hasMany(MsAccountMappingType::class, 'coa_id');
    }

    public function accountMappings()
    {
        return $this->hasMany(MsAccountMapping::class, 'coa_id');
    }

    public function accountMappingInventories()
    {
        return $this->hasMany(MsAccountMappingInventory::class, 'coa_id');
    }

    public function bomHeaders()
    {
        return $this->hasMany(\Keysoft\HelperLibrary\Models\Tenant\Master\Inventory\MsBomHD::class, 'foh_coa_id');
    }
}
