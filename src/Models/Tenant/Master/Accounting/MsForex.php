<?php

namespace Keysoft\HelperLibrary\Models\Tenant\Master\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Keysoft\HelperLibrary\Models\BaseModelTenant;
use Keysoft\HelperLibrary\Models\Tenant\Master\Common\MsCurrency;
use Keysoft\HelperLibrary\Traits\AuditedBy;

class MsForex extends BaseModelTenant
{
    use HasFactory, AuditedBy;

    protected $connection = 'tenant';
    protected $table = 'trans_rate_policy';

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'integer';
    protected $guarded = ['created_at', 'updated_at'];

    public function currency()
    {
        return $this->belongsTo(MsCurrency::class, 'currency_id');
    }
}
