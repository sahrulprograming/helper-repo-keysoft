<?php

namespace Keysoft\HelperLibrary\Models\Tenant\Master\Common;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Keysoft\HelperLibrary\Models\BaseModelTenant;
use Keysoft\HelperLibrary\Traits\AuditedBy;

class MsSubDistrict extends BaseModelTenant
{
    use HasFactory, AuditedBy;

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
