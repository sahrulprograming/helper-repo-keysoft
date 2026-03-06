<?php

namespace App\Models\Master\Accounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Keysoft\HelperLibrary\Models\Master\Common\MsCurrency;

class MsForex extends Model
{
    use HasFactory;
    protected $connection = 'pgsql';
    protected $table = 'trans_rate_policy';

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
}
