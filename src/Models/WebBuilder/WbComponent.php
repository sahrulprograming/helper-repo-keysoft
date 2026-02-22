<?php

namespace Keysoft\HelperLibrary\Models\WebBuilder;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Keysoft\HelperLibrary\Models\BaseModelTenant;

class WbComponent extends BaseModelTenant
{
    // --- Database Tables ---
    public const TABLE_NAME = 'wb_components';
    public const PIVOT_COMPONENT_PAGE_TABLE_NAME = 'wb_pivot_page_component';
    public const PIVOT_COMPONENT_TABLE_NAME = 'wb_pivot_components';

    // --- Cache Keys ---
    public const CACHE_LIST_KEY             = self::TABLE_NAME . '_list';

    // --- Relations ---
    public const RELATION_PAGES            = 'pages';
    public const RELATION_CHILDREN        = 'children';
    public const RELATION_PARENTS        = 'parents';


    protected $table = self::TABLE_NAME;
    protected $connection = 'tenant';
    protected $guarded = ['id'];

    public function getCacheKeys(): array|string
    {
        return [self::CACHE_LIST_KEY];
    }

    protected $casts = [
        'props' => 'array',
        'style' => 'array',
    ];

    /**
     * Relasi kembali ke wb_pages (Many-to-Many)
     */
    public function pages() : BelongsToMany
    {
        return $this->belongsToMany(
            WbPage::class,
            self::PIVOT_COMPONENT_PAGE_TABLE_NAME,
            'component_id',
            'page_id'
        );
    }

    /**
     * Relasi ke komponen anak (Nested Components) via pivot wb_pivot_components
     */
    public function children() : BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            self::PIVOT_COMPONENT_TABLE_NAME,
            'parent_component_id',
            'component_id'
        )
            ->withPivot(['sort_order'])
            ->orderBy(self::PIVOT_COMPONENT_TABLE_NAME . '.sort_order');
    }

    /**
     * Relasi ke komponen induk (Parent Components) via pivot wb_pivot_components
     */
    public function parents() : BelongsToMany
    {
        return $this->belongsToMany(
            WbComponent::class,
            self::PIVOT_COMPONENT_TABLE_NAME,
            'component_id',        // Karena kita mencari parent, ID kita bertindak sebagai child di pivot
            'parent_component_id'  // Target id yang mau diambil
        );
    }

    public function loadRecursiveChildren(array $visited = []): self
    {
        if (in_array($this->id, $visited)) {
            return $this;
        }

        $visited[] = $this->id;

        $this->load('children');

        foreach ($this->children as $child) {
            $child->loadRecursiveChildren($visited);
        }

        return $this;
    }
}
