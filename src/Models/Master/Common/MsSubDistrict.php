<?php

namespace Keysoft\HelperLibrary\Models\Master\Common;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsSubDistrict extends Model
{
    use HasFactory;

    protected $connection= 'tenant';
    protected $table = 'ms_sub_district';

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'integer';

    protected $guarded = ['created_at', 'updated_at'];

    public function district()
    {
        return $this->belongsTo(MsDistrict::class, 'district_id');
    }
}
