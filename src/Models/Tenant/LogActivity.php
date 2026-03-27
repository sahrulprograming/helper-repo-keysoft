<?php

namespace Keysoft\HelperLibrary\Models\Tenant;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Keysoft\HelperLibrary\Models\BaseModelTenant;

class LogActivity extends BaseModelTenant
{
    public const TABLE_NAME = 'log_activity';

    protected $table = self::TABLE_NAME;

    protected $connection = 'tenant';

    protected $guarded = [
        'id',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'response_code' => 'integer',
            'ref_id' => 'integer',
            'latency_ms' => 'integer',
            'payload' => 'array',
            'response' => 'array',
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(MsUser::class, 'user_id');
    }
}
