<?php

namespace Keysoft\HelperLibrary\Models\Tenant\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Keysoft\HelperLibrary\Models\BaseModelTenant;
use Keysoft\HelperLibrary\Models\Tenant\Master\Common\MsCity;
use Keysoft\HelperLibrary\Models\Tenant\Master\Common\MsCountry;
use Keysoft\HelperLibrary\Models\Tenant\Master\Common\MsDistrict;
use Keysoft\HelperLibrary\Models\Tenant\Master\Common\MsProvince;
use Keysoft\HelperLibrary\Models\Tenant\Master\Common\MsSubDistrict;
use Keysoft\HelperLibrary\Models\Tenant\Master\Inventory\MsWarehouse;
use Keysoft\HelperLibrary\Traits\AuditedBy;

class MsBranch extends BaseModelTenant
{
    use HasFactory, AuditedBy;

    protected $connection = 'tenant';
    protected $table = 'ms_branch';

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'integer';

    protected $guarded = ['created_at', 'updated_at'];

    protected $casts = [
        'json' => 'array',
    ];

    public function warehouse()
    {
        return $this->belongsTo(MsWarehouse::class, 'warehouse_id', 'id');
    }

    public function country()
    {
        return $this->belongsTo(MsCountry::class, 'country_id', 'id');
    }

    public function province()
    {
        return $this->belongsTo(MsProvince::class, 'province_id', 'id');
    }

    public function city()
    {
        return $this->belongsTo(MsCity::class, 'city_id', 'id');
    }

    public function district()
    {
        return $this->belongsTo(MsDistrict::class, 'district_id', 'id');
    }

    public function subdistrict()
    {
        return $this->belongsTo(MsSubDistrict::class, 'sub_district_id', 'id');
    }
}
