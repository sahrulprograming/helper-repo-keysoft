<?php

namespace Keysoft\HelperLibrary\Models\WebBuilder;

use Keysoft\HelperLibrary\Models\BaseModelTenant;

class WbLayout extends BaseModelTenant
{
    public const TABLE_NAME = 'wb_layouts';
    public const CACHE_LIST_KEY = self::TABLE_NAME . '_list';
    protected $table = self::TABLE_NAME;
    protected $connection = 'tenant';
    protected $guarded = ['id'];
    protected $casts = [
        'slots' => 'array',
        'config' => 'array',
    ];

    public function getCacheKeys(): array|string
    {
        return [self::CACHE_LIST_KEY];
    }

    public function pages()
    {
        return $this->hasMany(WbPage::class, 'layout_id');
    }
}
