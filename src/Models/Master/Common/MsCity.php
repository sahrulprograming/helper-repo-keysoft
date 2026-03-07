<?php

namespace Keysoft\HelperLibrary\Models\Master\Common;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Keysoft\HelperLibrary\Models\BaseModelTenant;
use Keysoft\HelperLibrary\Traits\AuditedBy;

class MsCity extends BaseModelTenant
{
    use HasFactory, AuditedBy;

    protected $connection= 'tenant';
    protected $table = 'ms_city';

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'integer';

    protected $guarded = ['created_at', 'updated_at'];

    public function province()
    {
        return $this->belongsTo(MsProvince::class, 'province_id');
    }

    public function districts()
    {
        return $this->hasMany(MsDistrict::class, 'city_id');
    }
}
