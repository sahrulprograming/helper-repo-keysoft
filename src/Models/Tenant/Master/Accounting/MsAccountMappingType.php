<?php

namespace Keysoft\HelperLibrary\Models\Tenant\Master\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    protected $connection = 'tenant';
    protected $table = 'ms_account_mapping_type';

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'integer';
    protected $guarded = ['created_at', 'updated_at'];

    protected $casts = [
        'json' => 'array',
    ];

    public function coas(): BelongsToMany
    {
        return $this->belongsToMany(MsCOA::class, 'ms_account_mapping', 'account_mapping_type_id', 'coa_id')
            ->withPivot(['account_no', 'status', 'created_by', 'updated_by', 'json'])
            ->withTimestamps();
    }

    public function coa()
    {
        return $this->belongsTo(MsCOA::class, 'coa_id');
    }
}
