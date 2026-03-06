<?php

namespace App\Models\Master\Accounting;

use App\Models\Master\Common\MsCurrency;
use Illuminate\Database\Eloquent\Model;
use App\Models\User\Supplier\MsSupplierShipment;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MsCOA extends Model
{
    use HasFactory;
    protected $connection = 'pgsql';
    protected $table = 'ms_coa';

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function currency()
    {
        return $this->belongsTo(MsCurrency::class, 'ms_currency_id');
    }

    public function parent()
    {
        return $this->belongsTo(MsCOA::class, 'parent_id');
    }

    public function category()
    {
        return $this->belongsTo(MsCategoryCOA::class, 'ms_category_coa_id');
    }

    public function shipmentAccount()
    {
        return $this->hasMany(MsSupplierShipment::class, 'shipment_account_no', 'account_no');
    }

    public function clearanceAccount()
    {
        return $this->hasMany(MsSupplierShipment::class, 'clearance_account_no', 'account_no');
    }
}
