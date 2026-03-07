<?php

namespace Keysoft\HelperLibrary\Models\WebBuilder;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Keysoft\HelperLibrary\Models\BaseModelTenant;
use Keysoft\HelperLibrary\Models\Tenant\MsOrgHierarchy;

class WbPage extends BaseModelTenant
{
    // --- Database Tables ---
    public const TABLE_NAME                 = 'wb_pages';
    public const PIVOT_COMPONENT_PAGE_TABLE_NAME = 'wb_pivot_page_component';

    // --- Cache Keys ---
    public const CACHE_LIST_KEY             = self::TABLE_NAME . '_list';

    // --- Relations ---
    public const RELATION_LAYOUT            = 'layout';
    public const RELATION_COMPONENTS        = 'components';

    protected $table = self::TABLE_NAME;
    protected $connection = 'tenant';
    protected $guarded = ['id'];

    protected $casts = [
        'category_id' => 'integer',
        'parent_id' => 'integer',
        'sort_order' => 'integer',
        'middleware' => 'array',
        'config' => 'array',
    ];

    public function getCacheKeys(): array|string
    {
        return [self::CACHE_LIST_KEY];
    }

    /**
     * Relasi ke wb_layouts (Belongs-To)
     */
    public function layout()
    {
        return $this->belongsTo(WbLayout::class, 'layout_id');
    }

    /**
     * Relasi ke wb_component (Many-to-Many) melalui wb_pivot_page_component
     */
    public function components()
    {
        return $this->belongsToMany(
            WbComponent::class,
            self::PIVOT_COMPONENT_PAGE_TABLE_NAME, // Nama tabel pivot
            'page_id',                 // Foreign key model ini di pivot
            'component_id'             // Foreign key model tujuan di pivot
        );
    }

    public function msOrgHierarchies(): BelongsToMany
    {
        return $this->belongsToMany(
            MsOrgHierarchy::class,
            MsOrgHierarchy::PIVOT_WB_SOURCE_TABLE_NAME,
            'ref_id',
            'org_hierarchy_id',
        )
            ->wherePivot('type', MsOrgHierarchy::SOURCE_TYPE_PAGE)
            ->withPivotValue('type', MsOrgHierarchy::SOURCE_TYPE_PAGE)
            ->withPivot(['type']);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(WbPageCategory::class, 'category_id');
    }
}
