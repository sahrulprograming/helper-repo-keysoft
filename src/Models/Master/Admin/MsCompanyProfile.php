<?php

namespace Keysoft\HelperLibrary\Models\Master\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Keysoft\HelperLibrary\Models\Master\Common\MsCountry;
use Keysoft\HelperLibrary\Models\Master\Common\MsCurrency;
use Keysoft\HelperLibrary\Models\BaseModelTenant;
use Keysoft\HelperLibrary\Traits\AuditedBy;

class MsCompanyProfile extends BaseModelTenant
{
    use HasFactory, AuditedBy;
    
    protected $connection = 'tenant';
    protected $table = 'ms_company_profile';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function country()
    {
        return $this->belongsTo(MsCountry::class, 'ms_country_id', 'id');
    }

    public function currency()
    {
        return $this->belongsTo(MsCurrency::class, 'ms_currency_id', 'id');
    }
}
