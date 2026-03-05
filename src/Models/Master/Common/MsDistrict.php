<?php

namespace Keysoft\HelperLibrary\Models\Master\Common;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsDistrict extends Model
{
    use HasFactory;

    protected $connection= 'tenant';
    protected $table = 'ms_district';

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'integer';

    protected $guarded = ['created_at', 'updated_at'];

    public function province()
    {
        return $this->belongsTo(MsProvince::class, 'province_id');
    }

    public function subDistricts()
    {
        return $this->hasMany(MsSubDistrict::class, 'district_id');
    }
}
