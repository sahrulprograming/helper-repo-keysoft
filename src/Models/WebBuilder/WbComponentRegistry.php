<?php

namespace Keysoft\HelperLibrary\Models\WebBuilder;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WbComponentRegistry extends Model
{
    use SoftDeletes;
    public const TABLE_NAME = 'wb_components_registry';
    public const RELATION_CATEGORY = 'registryCategory';
    protected $table = self::TABLE_NAME;
    protected $guarded = ['id'];

    protected $casts = [
        'default_props' => 'array',
        'default_style' => 'array',
    ];

    public function registryCategory(): BelongsTo
    {
        return $this->belongsTo(WbComponentRegistryCategory::class, 'registry_category_id');
    }
}
