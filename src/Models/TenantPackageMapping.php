<?php

namespace Keysoft\HelperLibrary\Models;

use App\Traits\AuditedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantPackageMapping extends Model
{
    use AuditedBy;

    protected $table = 'tenant_package_mapping';

    public $incrementing = true;

    protected $guarded = [
        'id',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(MsTenant::class, 'tenant_id');
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(MsPackage::class, 'package_id');
    }
}
