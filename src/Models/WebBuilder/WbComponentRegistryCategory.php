<?php

namespace Keysoft\HelperLibrary\Models\WebBuilder;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WbComponentRegistryCategory extends Model
{
    use SoftDeletes;
    public const TABLE_NAME = 'wb_component_registry_category';
    protected $table = self::TABLE_NAME;
    protected $guarded = ['id'];
}
