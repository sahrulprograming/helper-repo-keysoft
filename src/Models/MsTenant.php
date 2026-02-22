<?php

namespace Keysoft\HelperLibrary\Models;

use App\Traits\AuditedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MsTenant extends Model
{
    use AuditedBy;

    protected $table = 'ms_tenant';

    protected $guarded = [
        'id',
    ];


    // Relasi ke User
    public function users(): HasMany
    {
        return $this->hasMany(MsUser::class, 'tenant_id');
    }

    // Relasi Many-to-Many ke Package
    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(MsPackage::class, 'tenant_package_mapping', 'tenant_id', 'package_id')
                    ->withPivot('status', 'expired_at')
                    ->withTimestamps();
    }
}
