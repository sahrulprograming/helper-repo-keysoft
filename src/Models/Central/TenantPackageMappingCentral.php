<?php

namespace Keysoft\HelperLibrary\Models\Central;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantPackageMappingCentral extends Model
{

    protected $table = 'tenant_package_mapping';

    public $incrementing = true;

    protected $guarded = [
        'id',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(MsTenantCentral::class, 'tenant_id');
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(MsPackageCentral::class, 'package_id');
    }
}
