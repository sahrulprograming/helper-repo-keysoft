<?php


namespace Keysoft\HelperLibrary\Models;

use App\Traits\AuditedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MsUser extends Model
{
    use AuditedBy;

    protected $table = 'ms_users';
    public const PIVOT_USER_TENANT_TABLE_NAME = 'pivot_user_tenant';

    protected $guarded = [
        'id',
    ];

    protected $hidden = ['password'];


    public function tenants(): belongsToMany
    {
        return $this->belongsToMany(
            MsTenant::class,
            self::PIVOT_USER_TENANT_TABLE_NAME, // Nama tabel pivot
            'user_id',                 // Foreign key model ini di pivot
            'tenant_id'             // Foreign key model tujuan di pivot
        );
    }
}
