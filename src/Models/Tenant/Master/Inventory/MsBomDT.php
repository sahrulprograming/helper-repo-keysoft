<?php

namespace Keysoft\HelperLibrary\Models\Tenant\Master\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Keysoft\HelperLibrary\Models\BaseModelTenant;
use Keysoft\HelperLibrary\Traits\AuditedBy;

class MsBomDT extends BaseModelTenant
{
    use HasFactory, AuditedBy;

    protected $connection = 'tenant';
    protected $table = 'ms_bom_dt';

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'integer';

    protected $guarded = ['created_at', 'updated_at'];

    protected $casts = [
        'qty' => 'double',
        'tolerance_percentage' => 'double',
        'percentage_reverse' => 'double',
    ];

    public function header()
    {
        return $this->belongsTo(MsBomHD::class, 'hd_id', 'id');
    }

    public function part()
    {
        return $this->belongsTo(MsPart::class, 'part_id', 'id');
    }
}
