<?php

namespace Keysoft\HelperLibrary\Models;

use App\Traits\AuditedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Keysoft\HelperLibrary\Support\GeneralCipher;

class MsTenant extends Model
{
    use AuditedBy;

    protected $table = 'ms_tenant';

    protected $guarded = [
        'id',
    ];

    protected function dbPassword(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value === null
                ? null
                : $this->cipher()->decrypt($value),
            set: fn (?string $value) => $value === null
                ? null
                : $this->cipher()->encrypt($value),
        );
    }


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

    protected function cipher(): GeneralCipher
    {
        return new GeneralCipher();
    }
}
