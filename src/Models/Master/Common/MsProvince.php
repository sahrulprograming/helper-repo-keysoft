<?php

namespace Keysoft\HelperLibrary\Models\Master\Common;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Keysoft\HelperLibrary\Models\BaseModelTenant;

class MsProvince extends BaseModelTenant
{
    use HasFactory;

    protected $connection= 'tenant';
    protected $table = 'ms_province';

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'integer';

    protected $guarded = ['created_at', 'updated_at'];

    public function country()
    {
        return $this->belongsTo(MsCountry::class, 'country_id');
    }

    public function cities()
    {
        return $this->hasMany(MsCity::class, 'province_id');
    }
}
