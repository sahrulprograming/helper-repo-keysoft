<?php

namespace Keysoft\HelperLibrary\Models\Tenant\Master\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Keysoft\HelperLibrary\Models\BaseModelTenant;
use Keysoft\HelperLibrary\Traits\AuditedBy;

/**
 * Class Ms_User
 *
 * @property string $CreatedBy
 * @property string $EntryTime
 *
 * @package App\Models
 */

class MsAccountMapping extends BaseModelTenant
{
    use HasFactory, AuditedBy;

    protected $connection = 'tenant';
    protected $table = 'ms_account_mapping';

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'integer';
    protected $guarded = ['created_at', 'updated_at'];

    public function coa()
    {
        return $this->belongsTo(MsCOA::class, 'coa_id');
    }
}
