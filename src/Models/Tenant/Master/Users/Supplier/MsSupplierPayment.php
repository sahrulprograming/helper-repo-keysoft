<?php

namespace Keysoft\HelperLibrary\Models\Tenant\Master\Users\Supplier;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Keysoft\HelperLibrary\Models\BaseModelTenant;
use Keysoft\HelperLibrary\Models\Tenant\Master\Accounting\MsBank;
use Keysoft\HelperLibrary\Models\Tenant\Master\Common\MsCurrency;
use Keysoft\HelperLibrary\Traits\AuditedBy;

class MsSupplierPayment extends BaseModelTenant
{
    use HasFactory, AuditedBy;

    protected $connection = 'tenant';
    protected $table = 'ms_supplier_payment';

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'integer';

    protected $guarded = ['created_at', 'updated_at'];

    protected $casts = [
        'json' => 'array',
    ];

    // Buat Get data ketika true
    protected static function booted()
    {
        parent::booted();

        static::saving(function ($model) {

            if ($model->isDirty('is_approve')) {

                $payload = request()->attributes->get('jwt_payload');

                if (!$payload || !isset($payload['sub'])) {
                    throw new \Exception('Invalid JWT payload');
                }

                if ($model->is_approve) {
                    $model->approved_by = $payload['sub'];
                    $model->approved_at = now();
                } else {
                    $model->approved_by = null;
                    $model->approved_at = null;
                }
            }
        });
    }

    public function supplier()
    {
        return $this->belongsTo(MsSupplier::class, 'supplier_id', 'id');
    }

    public function currency()
    {
        return $this->belongsTo(MsCurrency::class, 'currency_id', 'id');
    }

    public function bank()
    {
        return $this->belongsTo(MsBank::class, 'bank_id', 'id');
    }
}
