<?php

namespace Keysoft\HelperLibrary\Models\Central;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Keysoft\HelperLibrary\Support\GeneralCipher;
use Keysoft\HelperLibrary\Traits\AuditedBy;
use RuntimeException;

class MsTenantCentral extends Model
{
    protected $table = 'ms_tenant';

    protected $guarded = [
        'id',
    ];

    protected function dbPassword(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $this->decryptDbPassword($value),
            set: fn (?string $value) => $value === null
                ? null
                : $this->cipher()->encrypt($value),
        );
    }


    // Relasi ke User
    public function users(): HasMany
    {
        return $this->hasMany(MsUserCentral::class, 'tenant_id');
    }

    // Relasi Many-to-Many ke Package
    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(MsPackageCentral::class, 'tenant_package_mapping', 'tenant_id', 'package_id')
                    ->withPivot('status', 'expired_at')
                    ->withTimestamps();
    }

    protected function cipher(): GeneralCipher
    {
        return new GeneralCipher();
    }

    protected function decryptDbPassword(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        $cipher = $this->cipher();

        if (! $cipher->isEncrypted($value)) {
            return $value;
        }

        try {
            return $cipher->decrypt($value);
        } catch (RuntimeException) {
            return $value;
        }
    }
}
