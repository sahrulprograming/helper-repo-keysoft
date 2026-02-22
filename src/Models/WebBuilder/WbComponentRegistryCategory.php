<?php

namespace Keysoft\HelperLibrary\Models\WebBuilder;

use App\Traits\AuditedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WbComponentRegistryCategory extends Model
{
    use SoftDeletes, AuditedBy;
    public const TABLE_NAME = 'wb_component_registry_category';
    protected $table = self::TABLE_NAME;
    protected $guarded = ['id'];
}
