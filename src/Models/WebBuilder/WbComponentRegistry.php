<?php

namespace Keysoft\HelperLibrary\Models\WebBuilder;

use App\Traits\AuditedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WbComponentRegistry extends Model
{
    use SoftDeletes, AuditedBy;
    public const TABLE_NAME = 'wb_components_registry';
    public const RELATION_CATEGORY = 'registryCategory';
    protected $table = self::TABLE_NAME;
    protected $guarded = ['id'];

    public function registryCategory(): BelongsTo
    {
        return $this->belongsTo(WbComponentRegistryCategory::class, 'registry_category_id');
    }
}
