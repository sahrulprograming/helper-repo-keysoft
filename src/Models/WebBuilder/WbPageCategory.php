<?php

namespace Keysoft\HelperLibrary\Models\WebBuilder;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Keysoft\HelperLibrary\Models\BaseModelTenant;

class WbPageCategory extends BaseModelTenant
  {
      public const TABLE_NAME = 'wb_page_categories';
      public const CACHE_LIST_KEY = self::TABLE_NAME . '_list';
      public const RELATION_PAGES = 'pages';

      protected $table = self::TABLE_NAME;
      protected $connection = 'tenant';
      protected $guarded = ['id'];

      public function getCacheKeys(): array|string
      {
          return [self::CACHE_LIST_KEY];
      }

      public function pages(): HasMany
      {
          return $this->hasMany(WbPage::class, 'category_id');
      }
  }
