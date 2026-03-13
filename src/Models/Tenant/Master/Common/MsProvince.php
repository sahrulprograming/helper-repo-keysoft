<?php

namespace Keysoft\HelperLibrary\Models\Tenant\Master\Common;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Keysoft\HelperLibrary\Models\BaseModelTenant;
use Keysoft\HelperLibrary\Traits\AuditedBy;

class MsProvince extends BaseModelTenant
{
    use HasFactory, AuditedBy;

    protected $connection= 'tenant';
    protected $table = 'ms_province';

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'integer';

    protected $guarded = ['created_at', 'updated_at'];

    protected $casts = [
        'json' => 'array',
    ];

    public function country()
    {
        return $this->belongsTo(MsCountry::class, 'country_id');
    }

    public function cities()
    {
        return $this->hasMany(MsCity::class, 'province_id');
    }
}
