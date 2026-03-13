<?php

namespace Keysoft\HelperLibrary\Models\Tenant\Master\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Keysoft\HelperLibrary\Models\BaseModelTenant;
use Keysoft\HelperLibrary\Traits\AuditedBy;

class MsAutoJournalDT extends BaseModelTenant
{
    use HasFactory, AuditedBy;
    
    protected $connection = 'tenant';
    protected $table = 'ms_auto_journal_dt';
    protected $guarded = ['id'];

    protected $casts = [
        'json' => 'array',
    ];

    public $timestamps = false;
}
