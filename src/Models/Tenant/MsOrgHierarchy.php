<?php

namespace Keysoft\HelperLibrary\Models\Tenant;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Keysoft\HelperLibrary\Models\BaseModelTenant;
use Keysoft\HelperLibrary\Models\WebBuilder\WbComponent;
use Keysoft\HelperLibrary\Models\WebBuilder\WbPage;

class MsOrgHierarchy extends BaseModelTenant
{
    public const TABLE_NAME = 'ms_org_hierarchy';
    public const PIVOT_WB_SOURCE_TABLE_NAME = 'pivot_wb_source_org_hierarchy';
    public const PIVOT_USER_TABLE_NAME = 'pivot_user_org_hierarchy';
    public const SOURCE_TYPE_PAGE = 'wb_pages';
    public const SOURCE_TYPE_COMPONENT = 'wb_components';

    protected $table = self::TABLE_NAME;

    protected $connection = 'tenant';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'json' => 'array',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function wbPages(): BelongsToMany
    {
        return $this->belongsToMany(
            WbPage::class,
            self::PIVOT_WB_SOURCE_TABLE_NAME,
            'org_hierarchy_id',
            'ref_id',
        )
            ->wherePivot('type', self::SOURCE_TYPE_PAGE)
            ->withPivotValue('type', self::SOURCE_TYPE_PAGE)
            ->withPivot(['type']);
    }

    public function wbComponents(): BelongsToMany
    {
        return $this->belongsToMany(
            WbComponent::class,
            self::PIVOT_WB_SOURCE_TABLE_NAME,
            'org_hierarchy_id',
            'ref_id',
        )
            ->wherePivot('type', self::SOURCE_TYPE_COMPONENT)
            ->withPivotValue('type', self::SOURCE_TYPE_COMPONENT)
            ->withPivot(['type']);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            MsUser::class,
            self::PIVOT_USER_TABLE_NAME,
            'org_hierarchy_id',
            'user_id',
        );
    }
}
