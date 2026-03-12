<?php

namespace Keysoft\HelperLibrary\Models\Tenant\Master\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Keysoft\HelperLibrary\Models\BaseModelTenant;
use Keysoft\HelperLibrary\Traits\AuditedBy;

class MsPartUnit extends BaseModelTenant
{
    use HasFactory, AuditedBy;

    protected $connection = 'tenant';
    protected $table = 'ms_part_unit';

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'integer';

    protected $guarded = ['created_at', 'updated_at'];

    protected $casts = [
        'conversion' => 'double',
    ];

    public function part()
    {
        return $this->belongsTo(MsPart::class, 'part_id', 'id');
    }

    public function unit()
    {
        return $this->belongsTo(MsUnit::class, 'unit_id', 'id');
    }
}
