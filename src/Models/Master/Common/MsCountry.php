<?php

namespace Keysoft\HelperLibrary\Models\Master\Common;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Keysoft\HelperLibrary\Models\BaseModelTenant;

class MsCountry extends BaseModelTenant
{
    use HasFactory;

    protected $connection= 'tenant';
    protected $table = 'ms_country';

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'integer';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = ['created_at', 'updated_at'];

    // public function province()
    // {
    //     return $this->hasMany(MsProvince::class, 'CountryID', 'CountryID');
    // }
}
