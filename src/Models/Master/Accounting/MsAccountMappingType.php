<?php

namespace App\Models\Master\Accounting;

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

class MsAccountMappingType extends BaseModelTenant
{
    use HasFactory, AuditedBy;
    
    protected $connection= 'tenant';
    protected $table = 'ms_account_mapping_type';

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function account()
    {
        return $this->belongsTo(MsCOA::class, 'ms_coa_id');
    }
}
