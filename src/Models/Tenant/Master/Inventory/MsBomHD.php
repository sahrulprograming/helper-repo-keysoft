<?php

namespace Keysoft\HelperLibrary\Models\Tenant\Master\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Keysoft\HelperLibrary\Models\BaseModelTenant;
use Keysoft\HelperLibrary\Models\Tenant\Master\Accounting\MsCOA;
use Keysoft\HelperLibrary\Traits\AuditedBy;

class MsBomHD extends BaseModelTenant
{
    use HasFactory, AuditedBy;

    protected $connection = 'tenant';
    protected $table = 'ms_bom_hd';

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'integer';

    protected $guarded = ['created_at', 'updated_at'];

    protected $casts = [
        'json' => 'array',
        'foh_amount' => 'double',
    ];

    public function part()
    {
        return $this->belongsTo(MsPart::class, 'part_id', 'id');
    }

    public function fohCoa()
    {
        return $this->belongsTo(MsCOA::class, 'foh_coa_id', 'id');
    }

    public function details()
    {
        return $this->hasMany(MsBomDT::class, 'hd_id', 'id');
    }
}
