<?php

namespace Keysoft\HelperLibrary\Models\Tenant\Master\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Keysoft\HelperLibrary\Models\BaseModelTenant;
use Keysoft\HelperLibrary\Traits\AuditedBy;

class MsPartCategory extends BaseModelTenant
{
    use HasFactory, AuditedBy;

    protected $connection = 'tenant';
    protected $table = 'ms_part_category';

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'integer';

    protected $guarded = ['created_at', 'updated_at'];

    public function parts()
    {
        return $this->hasMany(MsPart::class, 'category_id', 'id');
    }
}
