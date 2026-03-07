<?php

namespace Keysoft\HelperLibrary\Models\Central;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MsPackageCentral extends Model
{

    protected $table = 'ms_packages';

    protected $guarded = [
        'id',
    ];


    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(MsTenantCentral::class, 'tenant_package_mapping', 'package_id', 'tenant_id')
                    ->withPivot('status', 'expired_at')
                    ->withTimestamps();
    }
}
