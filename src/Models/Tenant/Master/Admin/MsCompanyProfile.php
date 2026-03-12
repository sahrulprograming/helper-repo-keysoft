<?php

namespace Keysoft\HelperLibrary\Models\Tenant\Master\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Keysoft\HelperLibrary\Models\BaseModelTenant;
use Keysoft\HelperLibrary\Models\Tenant\Master\Common\MsCountry;
use Keysoft\HelperLibrary\Models\Tenant\Master\Common\MsCurrency;
use Keysoft\HelperLibrary\Traits\AuditedBy;

class MsCompanyProfile extends BaseModelTenant
{
    use HasFactory, AuditedBy;

    protected $connection = 'tenant';
    protected $table = 'ms_company_profile';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'integer';
    protected $guarded = ['created_at', 'updated_at'];

    public function country()
    {
        return $this->belongsTo(MsCountry::class, 'country_id', 'id');
    }

    public function currency()
    {
        return $this->belongsTo(MsCurrency::class, 'currency_id', 'id');
    }
}
