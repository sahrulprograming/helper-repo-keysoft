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

        try {
            $cipher = $this->cipher();

            if ($cipher->isEncrypted($value)) {
                return $cipher->decrypt($value);
            }

            $prefix = $this->detectCipherPrefix($value);

            if ($prefix === null) {
                return $value;
            }

            return (new GeneralCipher(prefix: $prefix))->decrypt($value);
        } catch (RuntimeException) {
            return $value;
        }
    }

    protected function detectCipherPrefix(string $value): ?string
    {
        if (substr_count($value, ':') < 3) {
            return null;
        }

        $prefix = trim((string) strtok($value, ':'));

        if ($prefix === '' || str_contains($prefix, ' ')) {
            return null;
        }

        return $prefix;
    }
}
