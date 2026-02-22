<?php

namespace Keysoft\HelperLibrary\Models\WebBuilder;

use Keysoft\HelperLibrary\Models\BaseModelTenant;

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
}
