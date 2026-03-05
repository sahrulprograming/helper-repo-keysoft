<?php

namespace Keysoft\HelperLibrary\Models\Master\Common;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Keysoft\HelperLibrary\Models\BaseModelTenant;

class MsDistrict extends BaseModelTenant
{
    use HasFactory;

    protected $connection= 'tenant';
    protected $table = 'ms_district';

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'integer';

    protected $guarded = ['created_at', 'updated_at'];

    public function city()
    {
        return $this->belongsTo(MsCity::class, 'city_id');
    }

    public function subDistricts()
    {
        return $this->hasMany(MsSubDistrict::class, 'district_id');
    }
}
